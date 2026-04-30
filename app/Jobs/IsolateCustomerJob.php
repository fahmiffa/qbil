<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Services\MikrotikService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class IsolateCustomerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $customer;
    public $tries = 3;
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Pengecekan: Jika pelanggan sudah membayar tagihan bulan ini, batalkan isolir
            $currentPeriod = now()->format('Y-m');
            $hasUnpaid = $this->customer->invoices()
                ->where('billing_period', $currentPeriod)
                ->where('status', 'unpaid')
                ->exists();
                
            if (!$hasUnpaid) {
                Log::info("Isolir dibatalkan untuk {$this->customer->name} karena tagihan periode {$currentPeriod} sudah dibayar.");
                return;
            }

            $user = $this->customer->user;

            if ($user && $user->hasFeature('mikrotik')) {
                $router = $user->router;
                if ($router) {
                    try {
                        $mikrotik = MikrotikService::getInstance($router);
                        
                        if ($this->customer->service_type === 'static' && $this->customer->ip_address) {
                            $mikrotik->addToAddressList($this->customer->ip_address, 'ISOLIR', 'Jatuh Tempo: ' . $this->customer->name);
                            Log::info("Customer {$this->customer->name} (Static) added to ISOLIR address list FROM {$this->customer->user->name}");
                        } elseif ($this->customer->service_type === 'pppoe' && $this->customer->username) {
                            $mikrotik->disablePppSecret($this->customer->username);
                            Log::info("Customer {$this->customer->name} (PPPoE) disabled due to expiration FROM {$this->customer->user->name}");
                        }
                    } catch (\Exception $e) {
                        Log::error("Failed to isolate customer {$this->customer->name} on Mikrotik: " . $e->getMessage());
                    }
                } else {
                    Log::warning("Router not configured for user {$user->id} during isolation of customer {$this->customer->id}");
                }
            }

            // Update status in local DB if not already
            if ($this->customer->status !== 'suspended') {
                $this->customer->update([
                    'status' => 'suspended',
                    'isolated_at' => now(),
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Failed to isolate customer {$this->customer->name}: " . $e->getMessage());
            throw $e; // Rethrow for retry
        }
    }
}
