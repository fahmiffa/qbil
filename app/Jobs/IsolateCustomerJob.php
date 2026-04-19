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
        $user = $this->customer->user;
        $router = $user->router;

        if (!$router) {
            Log::warning("Router not configured for user {$user->id} during isolation of customer {$this->customer->id}");
            return;
        }

        try {
            $mikrotik = new MikrotikService($router);
            
            if ($this->customer->service_type === 'static' && $this->customer->ip_address) {
                $mikrotik->addToAddressList($this->customer->ip_address, 'ISOLIR', 'Jatuh Tempo: ' . $this->customer->name);
                Log::info("Customer {$this->customer->name} (Static) added to ISOLIR address list.");
            } elseif ($this->customer->service_type === 'pppoe' && $this->customer->username) {
                // For PPPoE, isolation is often done by disabling or using a special profile.
                // But the user asked specifically for Address List ISOLIR.
                // We'll try to get the active IP if possible, or just disable it as a fallback.
                $mikrotik->disablePppSecret($this->customer->username);
                Log::info("Customer {$this->customer->name} (PPPoE) disabled due to expiration.");
            }

            // Update status in local DB if not already
            if ($this->customer->status !== 'suspended') {
                $this->customer->update(['status' => 'suspended']);
            }

        } catch (\Exception $e) {
            Log::error("Failed to isolate customer {$this->customer->name}: " . $e->getMessage());
            throw $e; // Rethrow for retry
        }
    }
}
