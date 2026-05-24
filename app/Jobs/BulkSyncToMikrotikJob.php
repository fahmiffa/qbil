<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\User;
use App\Models\Router;
use App\Services\MikrotikService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BulkSyncToMikrotikJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600; // 10 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(public User $user) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!$this->user->hasFeature('mikrotik')) {
            return;
        }

        $router = Router::where('user_id', $this->user->id)->first();
        if (!$router) {
            Log::warning("BulkSyncToMikrotikJob: No router found for user {$this->user->id}");
            return;
        }

        try {
            $customers = Customer::where('user_id', $this->user->id)->get();

            // Dapatkan semua router milik user
            $userRouters = \App\Models\Router::where('user_id', $this->user->id)
                ->oldest()->get()->keyBy('id');

            // Router default (pertama) sebagai fallback
            $defaultRouter = $userRouters->first();

            if (!$defaultRouter) {
                Log::warning("BulkSyncToMikrotikJob: No router found for user {$this->user->id}");
                return;
            }

            // Cache MikrotikService per router_id agar tidak reconnect berulang
            $mikrotikCache = [];
            $getMikrotik = function (int $routerId) use ($userRouters, $defaultRouter, &$mikrotikCache) {
                $cacheKey = $routerId ?: 0;
                if (!isset($mikrotikCache[$cacheKey])) {
                    $router = $userRouters->get($routerId) ?? $defaultRouter;
                    if ($router && !$router->is_active) {
                        $mikrotikCache[$cacheKey] = null;
                        return null;
                    }
                    $mikrotikCache[$cacheKey] = MikrotikService::getInstance($router);
                }
                return $mikrotikCache[$cacheKey];
            };

            $total   = $customers->count();
            $success = 0;
            $failed  = 0;

            foreach ($customers as $customer) {
                try {
                    // Gunakan router customer, fallback ke default
                    $mikrotik = $getMikrotik($customer->router_id ?? 0);

                    if (!$mikrotik) {
                        Log::info("BulkSyncToMikrotikJob: Skipping customer {$customer->id} because router is disabled.");
                        continue;
                    }

                    $package   = $customer->package;
                    $rateLimit = $package ? $package->getMikrotikRateLimit() : '0M/0M';
                    $profile   = $customer->ppp_profile ?? $package?->mikrotik_profile ?? 'default';

                    if ($customer->service_type === 'static' && $customer->mac_address && $customer->ip_address) {
                        // Ensure it's static
                        $mikrotik->makeLeaseStatic($customer->mac_address);

                        // Update/Create Lease
                        $mikrotik->updateDhcpLeaseByMac(
                            $customer->mac_address,
                            $customer->mac_address,
                            $customer->ip_address,
                            $customer->dhcp_server ?: 'all',
                            $customer->name,
                            $rateLimit
                        );
                    } elseif ($customer->service_type === 'pppoe' && $customer->username) {
                        // Update/Create PPP Secret
                        $mikrotik->updatePppSecret(
                            $customer->username,
                            $customer->username,
                            $customer->password,
                            $profile,
                            $customer->name
                        );
                    }

                    // Re-apply status (suspension/activation)
                    if ($customer->status === 'suspended') {
                        $this->applySuspension($mikrotik, $customer);
                    } else {
                        $this->applyActivation($mikrotik, $customer);
                    }

                    $success++;
                } catch (\Exception $e) {
                    $failed++;
                    Log::error("BulkSyncToMikrotikJob: Failed for customer {$customer->id}: " . $e->getMessage());
                }
            }

            Log::info("BulkSyncToMikrotikJob: Completed for user {$this->user->id}. Success: {$success}, Failed: {$failed}, Total: {$total}");

            // Clear cache untuk semua router yang dipakai
            foreach ($mikrotikCache as $mk) {
                $mk->clearCache();
            }
        } catch (\Exception $e) {
            Log::error("BulkSyncToMikrotikJob: Fatal error: " . $e->getMessage());
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
            // Ensure lease is enabled so they can be caught by Address List (ISOLIR)
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
}
