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
        try {
            $router = Router::where('user_id', $this->customer->user_id)->first();
            if (!$router) {
                Log::warning("No router found for customer {$this->customer->id} (User: {$this->customer->user_id})");
                return;
            }

            $mikrotik = new MikrotikService($router);
            $package  = $this->customer->package;
            $rateLimit = $package ? ($package->speed_upload . '/' . $package->speed_download) : '0M/0M';

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
        if ($customer->service_type === 'static') {
            if ($customer->mac_address) $mikrotik->removeDhcpLeaseByMac($customer->mac_address);
            if ($customer->ip_address) $mikrotik->removeDhcpLeaseByIp($customer->ip_address);

            if ($customer->mac_address && $customer->ip_address) {
                $mikrotik->addDhcpLease(
                    $customer->mac_address, 
                    $customer->ip_address, 
                    $customer->dhcp_server ?: 'all', 
                    $customer->name, 
                    $rateLimit
                );
            }
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
            try { $mikrotik->removeSimpleQueue($customer->name); } catch(\Exception $e) {}
            
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
            if ($customer->ip_address) {
                $mikrotik->addToAddressList($customer->ip_address, 'ISOLIR', 'Suspended: ' . $customer->name);
            }
        } elseif ($customer->service_type === 'static' && $customer->mac_address) {
            $mikrotik->setDhcpLeaseStateByMac($customer->mac_address, true);
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
}
