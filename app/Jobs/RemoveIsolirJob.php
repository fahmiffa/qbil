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
        $user = $this->customer->user;
        $router = $user->router;

        if (!$router) {
            Log::warning("Router tidak dikonfigurasi untuk user {$user->id} saat mencoba hapus block ISLOR pelanggan {$this->customer->id}");
            return;
        }

        try {
            $mikrotik = new MikrotikService($router);
            
            // Ambil ip pelanggan dan jalankan perintah hapus dari address list 'ISLOR'
            if ($this->customer->ip_address) {
                
                // Menambahkan fallback untuk "ISOLIR" (karena default script isolir lain menggunakan nama ini)
                $mikrotik->removeFromAddressList($this->customer->ip_address, 'ISOLIR');
                
                Log::info("Customer {$this->customer->name} dengan IP {$this->customer->ip_address} berhasil dihapus dari firewall address-list ISLOR.");
            }

            // Jika tipe PPPoE dan sempat didisable saat jatuh tempo, maka enable kembali
            if ($this->customer->service_type === 'pppoe' && $this->customer->username) {
                $mikrotik->enablePppSecret($this->customer->username);
                Log::info("Secret PPPoE untuk {$this->customer->name} kembali di-enable.");
            }

            // Update status local
            if ($this->customer->status !== 'active') {
                $this->customer->update(['status' => 'active']);
            }

        } catch (\Exception $e) {
            Log::error("Gagal menghapus pelanggan {$this->customer->name} dari ISLOR: " . $e->getMessage());
            throw $e; // Rethrow agar Queue mencoba lagi (retry)
        }
    }
}
