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

class RemoveIsolirJob implements ShouldQueue
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
            $user = $this->customer->user;
            if ($user && $user->hasFeature('mikrotik')) {
                // Prioritas: router yang diasosiasikan ke customer, fallback ke router pertama user
                $router = $this->customer->router
                    ?? $user->routers()->oldest()->first();

                if ($router) {
                    if (!$router->is_active) {
                        Log::info("RemoveIsolirJob: Router '{$router->name}' is disabled. Skipping removal from isolation.");
                        return;
                    }
                    try {
                        $mikrotik = MikrotikService::getInstance($router);

                        if ($this->customer->ip_address) {
                            $mikrotik->removeFromAddressList($this->customer->ip_address, 'ISOLIR');
                            Log::info("Customer {$this->customer->name} berhasil dihapus dari address-list ISOLIR.");
                        }

                        if ($this->customer->service_type === 'pppoe' && $this->customer->username) {
                            $mikrotik->enablePppSecret($this->customer->username);
                            Log::info("Secret PPPoE untuk {$this->customer->name} kembali di-enable.");
                        }
                    } catch (\Exception $e) {
                        Log::error("Gagal menghapus pelanggan {$this->customer->name} dari ISOLIR on Mikrotik: " . $e->getMessage());
                    }
                }
            }

            // Update status local
            if ($this->customer->status !== 'active') {
                $this->customer->update(['status' => 'active']);
            }
        } catch (\Exception $e) {
            Log::error("Gagal menghapus pelanggan {$this->customer->name}: " . $e->getMessage());
            throw $e; // Rethrow agar Queue mencoba lagi (retry)
        }
    }
}
