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
        $period = $this->argument('period') ?: now()->format('Y-m');

        GenerateMonthlyInvoicesJob::dispatch($period);

        $this->info("Job GenerateMonthlyInvoices telah di-dispatch untuk periode: {$period}");
    }
}
