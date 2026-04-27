<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncAllCustomersJob implements ShouldQueue
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

        $customers = Customer::where('user_id', $this->user->id)->get();
        $today = now();

        foreach ($customers as $customer) {
            // Cek apakah ada invoice yang belum lunas dan sudah melewati tanggal jatuh tempo
            $overdueInvoice = \App\Models\Invoice::where('customer_id', $customer->id)
                ->where('status', '!=', 'paid')
                ->where('due_date', '<', $today->format('Y-m-d'))
                ->exists();

            if ($overdueInvoice && $customer->status === 'active') {
                $customer->update(['status' => 'suspended']);
                $customer->refresh(); // Ambil status terbaru
            }

            // Jalankan provisioning (Create/Update) ke Mikrotik
            // Status suspended akan otomatis memicu isolasi di Mikrotik
            ProvisionCustomerJob::dispatch($customer, 'update', [
                'status'      => $customer->status,
                'profile'     => $customer->package?->mikrotik_profile,
                'username'    => $customer->username,
                'mac_address' => $customer->mac_address,
            ]);
        }
    }
}
