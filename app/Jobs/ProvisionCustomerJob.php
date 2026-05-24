<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Router;
use App\Services\MikrotikService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProvisionCustomerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Customer $customer,
        public string $action = 'create', // create, update, delete
        public ?array $oldData = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Check if user has mikrotik feature
        $user = \App\Models\User::find($this->customer->user_id);
        if ($user && !$user->hasFeature('mikrotik')) {
            Log::info("Skipping ProvisionCustomerJob for customer {$this->customer->id} because MikroTik feature is disabled.");
            return;
        }

        try {
            // Prioritas: router yang diasosiasikan ke customer ini
            // Fallback: router pertama milik user (backward compatible untuk data lama)
            $router = $this->customer->router
                ?? Router::where('user_id', $this->customer->user_id)->oldest()->first();

            if (!$router) {
                Log::warning("No router found for customer {$this->customer->id} (User: {$this->customer->user_id})");
                return;
            }

            if (!$router->is_active) {
                Log::info("ProvisionCustomerJob: Router '{$router->name}' is disabled. Skipping provisioning.");
                return;
            }

            $mikrotik = MikrotikService::getInstance($router);

            // PULL DATA: Jika MAC kosong tapi ada IP, coba tarik dari MikroTik
            $this->pullDataIfMissing($mikrotik, $this->customer);

            $package  = $this->customer->package;
            $rateLimit = $package ? $package->getMikrotikRateLimit() : '0M/0M';

            switch ($this->action) {
                case 'create':
                    $this->provisionCreate($mikrotik, $this->customer, $rateLimit);
                    break;
                case 'update':
                    $this->provisionUpdate($mikrotik, $this->customer, $rateLimit);
                    break;
                case 'delete':
                    $this->provisionDelete($mikrotik, $this->customer);
                    break;
            }
        } catch (\Exception $e) {
            Log::error("Mikrotik Provisioning Error for customer {$this->customer->id}: " . $e->getMessage());
        }
    }

    private function provisionCreate(MikrotikService $mikrotik, Customer $customer, string $rateLimit): void
    {
        if ($customer->service_type === 'static' && $customer->mac_address && $customer->ip_address) {
            // Coba ubah dynamic menjadi static dulu jika ada
            $mikrotik->makeLeaseStatic($customer->mac_address);

            // Kemudian update atau create lease static-nya
            $mikrotik->updateDhcpLeaseByMac(
                $customer->mac_address,
                $customer->mac_address,
                $customer->ip_address,
                $customer->dhcp_server ?: 'all',
                $customer->name,
                $rateLimit
            );
        } else {
            // pppoe
            if ($customer->username) $mikrotik->removePppSecret($customer->username);

            $profile = $customer->ppp_profile ?? $customer->package?->mikrotik_profile ?? 'default';
            if ($customer->username && $customer->password) {
                $mikrotik->addPppSecret($customer->username, $customer->password, $profile, $customer->name);
            }
        }

        if ($customer->status === 'suspended') {
            $this->applySuspension($mikrotik, $customer);
        }
    }

    private function provisionUpdate(MikrotikService $mikrotik, Customer $customer, string $rateLimit): void
    {
        $oldStatus   = $this->oldData['status'] ?? null;
        $oldProfile  = $this->oldData['profile'] ?? null;
        $oldUsername = $this->oldData['username'] ?? $customer->username;
        $oldMac      = $this->oldData['mac_address'] ?? $customer->mac_address;

        $profile = $customer->ppp_profile ?? $customer->package?->mikrotik_profile ?? $oldProfile ?? 'default';

        if ($customer->service_type === 'pppoe') {
            $mikrotik->updatePppSecret($oldUsername, $customer->username, $customer->password, $profile, $customer->name);
        } elseif ($customer->service_type === 'static') {
            if ($customer->mac_address && $customer->ip_address) {
                $mikrotik->updateDhcpLeaseByMac($oldMac, $customer->mac_address, $customer->ip_address, $customer->dhcp_server ?: 'all', $customer->name, $rateLimit);
            }
        }

        // Handle Status Change
        if ($customer->status === 'suspended') {
            $this->applySuspension($mikrotik, $customer);
        } elseif ($customer->status === 'active' && $oldStatus === 'suspended') {
            $this->applyActivation($mikrotik, $customer);
        }
    }

    private function provisionDelete(MikrotikService $mikrotik, Customer $customer): void
    {
        if ($customer->service_type === 'pppoe' && $customer->username) {
            $mikrotik->removePppSecret($customer->username);
            if ($customer->ip_address) {
                $mikrotik->removeFromAddressList($customer->ip_address, 'ISOLIR');
            }
        } elseif ($customer->service_type === 'static') {
            // Cleanup legacy simple queue if any
            try {
                $mikrotik->removeSimpleQueue($customer->name);
            } catch (\Exception $e) {
            }

            if ($customer->mac_address) {
                $mikrotik->removeDhcpLeaseByMac($customer->mac_address);
            }
            if ($customer->ip_address) {
                $mikrotik->removeFromAddressList($customer->ip_address, 'ISOLIR');
            }
        }
    }

    private function applySuspension(MikrotikService $mikrotik, Customer $customer): void
    {
        if ($customer->service_type === 'pppoe') {
            $mikrotik->disablePppSecret($customer->username);
            $mikrotik->removePppActive($customer->username);
            if ($customer->ip_address) {
                $mikrotik->addToAddressList($customer->ip_address, 'ISOLIR', 'Suspended: ' . $customer->name);
            }
        } elseif ($customer->service_type === 'static' && $customer->mac_address) {
            $mikrotik->setDhcpLeaseStateByMac($customer->mac_address, false);
            if ($customer->ip_address) {
                $mikrotik->addToAddressList($customer->ip_address, 'ISOLIR', 'Suspended: ' . $customer->name);
            }
        }
    }

    private function applyActivation(MikrotikService $mikrotik, Customer $customer): void
    {
        if ($customer->service_type === 'pppoe') {
            $mikrotik->enablePppSecret($customer->username);
            if ($customer->ip_address) {
                $mikrotik->removeFromAddressList($customer->ip_address, 'ISOLIR');
            }
        } elseif ($customer->service_type === 'static' && $customer->mac_address) {
            $mikrotik->setDhcpLeaseStateByMac($customer->mac_address, false);
            if ($customer->ip_address) {
                $mikrotik->removeFromAddressList($customer->ip_address, 'ISOLIR');
            }
        }
    }

    private function pullDataIfMissing(MikrotikService $mikrotik, Customer $customer): void
    {
        try {
            // Case 1: Static - Pull MAC/Server if missing
            if ($customer->service_type === 'static' && empty($customer->mac_address) && !empty($customer->ip_address)) {
                $lease = $mikrotik->findLeaseByIp($customer->ip_address);
                if ($lease && !empty($lease['mac-address'])) {
                    $customer->update([
                        'mac_address' => $lease['mac-address'],
                        'dhcp_server' => $lease['server'] ?? $customer->dhcp_server
                    ]);
                    $customer->refresh();
                }
            }

            // Case 2: PPPOE - Sync Password from Mikrotik to DB
            if ($customer->service_type === 'pppoe' && !empty($customer->username)) {
                $secret = $mikrotik->getPppSecretByName($customer->username);
                if ($secret && isset($secret['password'])) {
                    // Update DB if password differs
                    if ($customer->password !== $secret['password']) {
                        $customer->update(['password' => $secret['password']]);
                        $customer->refresh();
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning("Failed to sync data from Mikrotik for customer #{$customer->id}: " . $e->getMessage());
        }
    }
}
