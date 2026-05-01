<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Jobs\IsolateCustomerJob;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckDueInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:check-due';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for customers past due date and isolate them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();
        
        $this->info("Memulai pengecekan isolir otomatis...");

        $users = \App\Models\User::with('appSetting')->get();

        $currentPeriod = $now->format('Y-m');

        foreach ($users as $user) {
            if (!$user->hasFeature('mikrotik')) continue;
            
            $setting = $user->appSetting;
            if (!$setting) continue;

            // Cek waktu eksekusi (Jam & Menit)
            $configTime = Carbon::parse($setting->isolate_time)->format('H:i');
            if ($now->format('H:i') !== $configTime) continue;

            $offsetDays = (int) $setting->isolate_days;
            $targetDate = $now->copy()->subDays($offsetDays);

            // Cari pelanggan yang due_date-nya sudah mencapai ambang batas isolir
            // Dan statusnya masih aktif (belum isolir)
            // SERTA memiliki invoice bulan ini yang belum lunas (unpaid)
            $customers = Customer::where('user_id', $user->id)
                ->where('status', 'active')
                ->whereNotNull('due_date')
                ->whereDate('due_date', '=', $targetDate)
                ->whereHas('invoices', function($query) use ($currentPeriod) {
                    $query->where('status', 'unpaid')
                        ->where('billing_period', $currentPeriod);
                })
                ->get();

            if ($customers->isEmpty()) {
                continue;
            }

            foreach ($customers as $customer) {
                // Tambahan: Pastikan memang belum bayar tagihan di periode jatuh tempo tersebut
                // Jika sudah bayar, status biasanya tetap active dan tidak masuk kriteria isolir.
                
                IsolateCustomerJob::dispatch($customer);
                $this->line(" - Dispatching isolir: {$customer->name} (User: {$user->name})");
            }
        }

        $this->info("Selesai.");
    }
}
