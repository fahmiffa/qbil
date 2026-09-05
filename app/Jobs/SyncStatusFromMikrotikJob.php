<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\User;
use App\Services\MikrotikService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RouterOS\Query;

class SyncStatusFromMikrotikJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!$this->user->hasFeature('mikrotik')) {
            return;
        }

        $router = $this->user->router;
        if (!$router) {
            return;
        }

        try {
            $mikrotik = MikrotikService::getInstance($router);

            // 1. Ambil data PPP Secrets (untuk pelanggan PPPoE)
            $secrets = $mikrotik->getPppSecrets();
            $disabledUsernames = [];
            $activeUsernames = [];

            foreach ($secrets as $secret) {
                if (isset($secret['name'])) {
                    $isDisabled = isset($secret['disabled']) && ($secret['disabled'] === 'true' || $secret['disabled'] === 'yes');
                    if ($isDisabled) {
                        $disabledUsernames[] = $secret['name'];
                    } else {
                        $activeUsernames[] = $secret['name'];
                    }
                }
            }

            // 2. Ambil data Address List 'ISOLIR' (untuk pelanggan Static IP)
            $isolatedIps = [];
            try {
                // Gunakan raw query karena metode getAddressList mungkin belum ada di service
                $query = (new Query('/ip/firewall/address-list/print'))->where('list', 'ISOLIR');
                // reflection / direct access ke client
                $reflection = new \ReflectionClass($mikrotik);
                $property = $reflection->getProperty('client');
                $property->setAccessible(true);
                $client = $property->getValue($mikrotik);
                
                $addressLists = $client->query($query)->read();
                foreach ($addressLists as $item) {
                    if (isset($item['address'])) {
                        $isolatedIps[] = $item['address'];
                    }
                }
            } catch (\Exception $e) {
                Log::error("Gagal mengambil address list ISOLIR: " . $e->getMessage());
            }

            // 3. Update status pelanggan di database
            $customers = Customer::where('user_id', $this->user->id)->get();
            $updatedCount = 0;

            foreach ($customers as $customer) {
                $newStatus = null;

                if ($customer->service_type === 'pppoe' && $customer->username) {
                    if (in_array($customer->username, $disabledUsernames)) {
                        $newStatus = 'suspended';
                    } elseif (in_array($customer->username, $activeUsernames)) {
                        $newStatus = 'active';
                    }
                } elseif ($customer->service_type === 'static' && $customer->ip_address) {
                    if (in_array($customer->ip_address, $isolatedIps)) {
                        $newStatus = 'suspended';
                    } else {
                        $newStatus = 'active'; // Jika IP tidak ada di list ISOLIR, berarti aktif
                    }
                }

                if ($newStatus && $customer->status !== $newStatus) {
                    $customer->update([
                        'status' => $newStatus,
                        'isolated_at' => $newStatus === 'suspended' ? now() : null,
                        'activated_at' => $newStatus === 'active' ? now() : $customer->activated_at,
                    ]);
                    $updatedCount++;
                }
            }

            Log::info("Sync Status from Mikrotik completed for User {$this->user->name}. Updated {$updatedCount} customers.");

        } catch (\Exception $e) {
            Log::error("Gagal menjalankan SyncStatusFromMikrotikJob: " . $e->getMessage());
        }
    }
}
