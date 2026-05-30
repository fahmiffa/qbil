<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class VoucherOrderCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'voucher:cleanup';
    protected $description = 'Hapus pesanan voucher yang belum dibayar dalam 1x24 jam';

    public function handle()
    {
        $count = \App\Models\VoucherOrder::where('payment_status', 'unpaid')
            ->where('created_at', '<', now()->subHours(24))
            ->delete();

        if ($count > 0) {
            $this->info("Berhasil menghapus {$count} pesanan voucher kedaluwarsa.");
        } else {
            $this->info("Tidak ada pesanan voucher kedaluwarsa untuk dihapus.");
        }
    }
}
