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

        // Cari user yang memiliki setting isolate_time (tidak harus sama persis, yang penting ada)
        $users = \App\Models\User::whereHas('appSetting', function ($query) {
            $query->whereNotNull('isolate_time');
        })->with('appSetting')->get();

        if ($users->isEmpty()) {
            return;
        }

        $currentPeriod = $now->format('Y-m');

        foreach ($users as $user) {
            if (!$user->hasFeature('static') && !$user->hasFeature('pppoe')) continue;

            $setting = $user->appSetting;
            if (!$setting || empty($setting->isolate_time)) continue;

            // 1. Cek apakah jam saat ini sudah mencapai atau melewati jam isolir yang ditentukan
            $isolateTime = Carbon::parse($setting->isolate_time)->format('H:i');
            if ($currentTime < $isolateTime) {
                // Belum masuk jam isolir untuk user ini
                continue;
            }

            // 2. Cek apakah isolir harian untuk user ini sudah pernah dijalankan hari ini
            $cacheKey = "isolate_run_" . $user->id . "_" . $now->format('Y-m-d');
            if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                continue;
            }

            // Tandai sudah berjalan hari ini untuk user ini
            \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->endOfDay());

            $offsetDays = (int) ($setting->isolate_days ?? 0);
            $targetDate = $now->copy()->subDays($offsetDays)->toDateString();

            // 3. Cari pelanggan aktif yang due_date-nya sudah <= targetDate dan memiliki tagihan unpaid
            // Menggunakan <= memastikan pelanggan yang terlewat (akibat server mati kemarin/tadi) tetap terisolir
            $customers = Customer::where('user_id', $user->id)
                ->where('status', 'active')
                ->whereHas('invoices', function ($query) use ($targetDate, $currentPeriod) {
                    $query->where('status', 'unpaid')
                        ->where(function ($sub) use ($targetDate, $currentPeriod) {
                            $sub->whereDate('due_date', '<=', $targetDate)
                                ->orWhere(function ($s) use ($currentPeriod) {
                                    $s->whereNull('due_date')
                                        ->where('billing_period', '<=', $currentPeriod);
                                });
                        });
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
