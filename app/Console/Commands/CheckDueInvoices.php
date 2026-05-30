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
        $currentTime = $now->format('H:i');

        // Cari user yang memiliki setting isolate_time cocok dengan waktu sekarang
        $users = \App\Models\User::whereHas('appSetting', function ($query) use ($currentTime) {
            $query->where('isolate_time', $currentTime);
        })->with('appSetting')->get();

        if ($users->isEmpty()) {
            return;
        }

        $currentPeriod = $now->format('Y-m');

        foreach ($users as $user) {
            if (!$user->hasFeature('static') && !$user->hasFeature('pppoe')) continue;

            $setting = $user->appSetting;
            if (!$setting) continue;

            // Karena kita filter berdasarkan H:i tepat, kita tidak perlu lagi mengecek manual lessThan.
            // Tetap gunakan cache harian sebagai pengaman double execution jika scheduler terpanggil ulang.
            $cacheKey = "isolate_run_" . $user->id . "_" . $now->format('Y-m-d');
            if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                continue;
            }

            // Tandai sudah berjalan hari ini untuk user ini
            \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->endOfDay());

            $offsetDays = (int) $setting->isolate_days;
            $targetDate = $now->copy()->subDays($offsetDays);

            // Cari pelanggan yang due_date-nya sudah mencapai ambang batas isolir
            $customers = Customer::where('user_id', $user->id)
                ->where('status', 'active')
                ->whereNotNull('due_date')
                ->whereDate('due_date', '=', $targetDate)
                ->whereHas('invoices', function ($query) use ($currentPeriod) {
                    $query->where('status', 'unpaid')
                        ->where('billing_period', $currentPeriod);
                })
                ->get();

            if ($customers->isEmpty()) {
                continue;
            }

            foreach ($customers as $customer) {
                IsolateCustomerJob::dispatch($customer);

                $msg = "[CheckDueInvoices] Dispatching isolir: {$customer->name} (User: {$user->name})";
                $this->line(" - " . $msg);
                Log::info($msg);

                Log::build([
                    'driver' => 'single',
                    'path' => storage_path('logs/isolir.log'),
                ])->info("PELANGGAN TERISOLIR: {$customer->name} | ID: {$customer->id_pelanggan} | Username: {$customer->username} | User Admin: {$user->name}");
            }
        }
    }
}
