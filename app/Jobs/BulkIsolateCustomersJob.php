<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Router;
use App\Models\User;
use App\Services\MikrotikService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BulkIsolateCustomersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600; // 10 minutes
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param array $customerIds Optional list of customer IDs to isolate. If empty, isolates all overdue customers.
     */
    public function __construct(
        public User $user,
        public array $customerIds = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $query = Customer::where('user_id', $this->user->id)
                ->where('status', 'active');

            if (!empty($this->customerIds)) {
                $query->whereIn('id', $this->customerIds);
            } else {
                $query->whereHas('invoices', function ($q) {
                    $q->where('status', 'unpaid')
                        ->where(function ($sub) {
                            $sub->whereDate('due_date', '<=', now()->toDateString())
                                ->orWhere(function ($s) {
                                    $s->whereNull('due_date')
                                        ->where('billing_period', '<=', now()->format('Y-m'));
                                });
                        });
                });
            }

            $customers = $query->get();

            if ($customers->isEmpty()) {
                Log::info("BulkIsolateCustomersJob: No active customers to isolate for user {$this->user->id}");
                return;
            }

            $hasMikrotik = $this->user->hasFeature('mikrotik');
            $userRouters = Router::where('user_id', $this->user->id)->oldest()->get()->keyBy('id');
            $defaultRouter = $userRouters->first();

            $mikrotikCache = [];
            $getMikrotik = function ($routerId) use ($userRouters, $defaultRouter, &$mikrotikCache) {
                $cacheKey = $routerId ?: 0;
                if (!isset($mikrotikCache[$cacheKey])) {
                    $router = ($routerId ? $userRouters->get($routerId) : null) ?? $defaultRouter;
                    if (!$router || !$router->is_active) {
                        $mikrotikCache[$cacheKey] = null;
                        return null;
                    }
                    try {
                        $mikrotikCache[$cacheKey] = MikrotikService::getInstance($router);
                    } catch (\Exception $e) {
                        Log::error("BulkIsolateCustomersJob: Failed to connect to router {$router->id}: " . $e->getMessage());
                        $mikrotikCache[$cacheKey] = null;
                    }
                }
                return $mikrotikCache[$cacheKey];
            };

            $total = $customers->count();
            $success = 0;
            $failed = 0;

            foreach ($customers as $customer) {
                try {
                    // Check if customer still has unpaid invoice
                    $hasUnpaid = $customer->invoices()
                        ->where('status', 'unpaid')
                        ->where(function ($sub) {
                            $sub->whereDate('due_date', '<=', now()->toDateString())
                                ->orWhere(function ($s) {
                                    $s->whereNull('due_date')
                                        ->where('billing_period', '<=', now()->format('Y-m'));
                                });
                        })
                        ->exists();

                    if (!$hasUnpaid) {
                        Log::info("BulkIsolateCustomersJob: Isolir dibatalkan untuk {$customer->name} karena tidak ada tagihan tertunggak.");
                        continue;
                    }

                    // Mikrotik Isolation
                    if ($hasMikrotik) {
                        $mikrotik = $getMikrotik($customer->router_id);
                        if ($mikrotik) {
                            try {
                                if ($customer->service_type === 'static' && $customer->ip_address) {
                                    $mikrotik->addToAddressList($customer->ip_address, 'ISOLIR', 'Jatuh Tempo: ' . $customer->name);
                                    Log::info("BulkIsolateCustomersJob: Customer {$customer->name} (Static) added to ISOLIR address list");
                                } elseif ($customer->service_type === 'pppoe' && $customer->username) {
                                    $mikrotik->disablePppSecret($customer->username);
                                    $mikrotik->removePppActive($customer->username);
                                    if ($customer->ip_address) {
                                        $mikrotik->addToAddressList($customer->ip_address, 'ISOLIR', 'Jatuh Tempo: ' . $customer->name);
                                    }
                                    Log::info("BulkIsolateCustomersJob: Customer {$customer->name} (PPPoE) disabled due to expiration");
                                }
                            } catch (\Exception $e) {
                                Log::error("BulkIsolateCustomersJob: Failed to isolate customer {$customer->name} on Mikrotik: " . $e->getMessage());
                            }
                        }
                    }

                    // Update status in local DB
                    if ($customer->status !== 'suspended') {
                        $customer->update([
                            'status' => 'suspended',
                            'isolated_at' => now(),
                        ]);

                        Log::build([
                            'driver' => 'single',
                            'path' => storage_path('logs/isolir.log'),
                        ])->info("PELANGGAN TERISOLIR (MASSAL): {$customer->name} | ID: {$customer->id_pelanggan} | Username: {$customer->username} | User Admin: {$this->user->name}");
                    }

                    $success++;
                } catch (\Exception $e) {
                    $failed++;
                    Log::error("BulkIsolateCustomersJob: Failed for customer {$customer->id}: " . $e->getMessage());
                }
            }

            Log::info("BulkIsolateCustomersJob: Completed for user {$this->user->id}. Success: {$success}, Failed: {$failed}, Total: {$total}");

            // Notify admin
            if ($success > 0) {
                $this->user->notify(new \App\Notifications\SystemReportNotification(
                    'Isolir Massal Selesai',
                    "Sebanyak {$success} pelanggan telah berhasil diisolir karena memiliki tunggakan tagihan.",
                    'notif'
                ));
            }
        } catch (\Exception $e) {
            Log::error("BulkIsolateCustomersJob: Fatal error: " . $e->getMessage());
            throw $e;
        }
    }
}
