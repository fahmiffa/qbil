<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Jobs\IsolateCustomerJob;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
        
        // Log::info("[CheckDueInvoices] Memulai pengecekan isolir otomatis...");
        // $this->info("Memulai pengecekan isolir otomatis...");

        $users = \App\Models\User::with('appSetting')->get();

        $currentPeriod = $now->format('Y-m');

        foreach ($users as $user) {
            if (!$user->hasFeature('static') && !$user->hasFeature('pppoe')) continue;
            
            $setting = $user->appSetting;
            if (!$setting) continue;

            // Cek waktu eksekusi (Jam & Menit) - Menggunakan cache harian agar tidak terlewat jika ada delay scheduler
            $cacheKey = "isolate_run_" . $user->id . "_" . $now->format('Y-m-d');
            if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                continue;
            }

            $configTimeParsed = Carbon::parse($setting->isolate_time);
            $isolateDateTime = $now->copy()->setTime($configTimeParsed->hour, $configTimeParsed->minute, 0);

            if ($now->lessThan($isolateDateTime)) {
                continue;
            }

            // Tandai sudah berjalan hari ini untuk user ini
            \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->endOfDay());

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
                
                $msg = "[CheckDueInvoices] Dispatching isolir: {$customer->name} (User: {$user->name})";
                $this->line(" - " . $msg);
                Log::info($msg);

                // Log khusus ke isolir.log
                Log::build([
                    'driver' => 'single',
                    'path' => storage_path('logs/isolir.log'),
                ])->info("PELANGGAN TERISOLIR: {$customer->name} | ID: {$customer->id_pelanggan} | Username: {$customer->username} | User Admin: {$user->name}");
            }
        }

        // Log::info("[CheckDueInvoices] Selesai.");
        // $this->info("Selesai.");
    }
}
