<?php

namespace App\Console\Commands;

use App\Jobs\GenerateMonthlyInvoicesJob;
use Illuminate\Console\Command;

class GenerateMonthlyInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:generate {period?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate otomatis tagihan bulanan untuk seluruh pelanggan aktif (dispatch ke queue)';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $period = $this->argument('period') ?: '';
        $now = now();

        // Cari user yang memiliki setting invoice_gen_time cocok dengan waktu sekarang (H:i)
        // Jika period diisi manual via CLI, kita abaikan filter waktu dan proses semua.
        if ($period) {
            GenerateMonthlyInvoicesJob::dispatch($period);
            $this->info("Job GenerateMonthlyInvoices telah di-dispatch untuk periode: {$period}");
            return;
        }

        $currentTime = $now->format('H:i');

        $users = \App\Models\User::whereHas('appSetting', function ($query) use ($currentTime) {
            $query->where('invoice_gen_time', $currentTime);
        })->get();

        if ($users->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            GenerateMonthlyInvoicesJob::dispatch($period, $user->id);
            // $this->info("Job GenerateMonthlyInvoices di-dispatch untuk user: {$user->name}");
        }
    }
}
