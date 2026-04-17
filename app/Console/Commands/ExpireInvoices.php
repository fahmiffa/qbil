<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ExpireInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Batalkan invoice unpaid yang sudah lewat jatuh tempo untuk mereset kode unik';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pengecekan invoice kadaluarsa...');

        // Kita batalkan invoice yang unpaid dan tanggal due_date-nya sudah lewat lebih dari 2 hari
        // agar memberi waktu pelanggan membayar sedikit terlambat sebelum kode uniknya dilepas.
        $expiredCount = Invoice::where('status', 'unpaid')
            ->where('due_date', '<', now()->subDays(2))
            ->update(['status' => 'canceled']);

        $this->info("Berhasil membatalkan $expiredCount invoice. Kode unik sekarang sudah tersedia kembali.");
    }
}
