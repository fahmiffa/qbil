<?php

namespace App\Console\Commands;

use App\Jobs\SendInvoiceRemindersJob;
use Illuminate\Console\Command;

class SendInvoiceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:remind';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim pengingat tagihan via WhatsApp (dispatch ke queue)';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        SendInvoiceRemindersJob::dispatch();

        $this->info('Job SendInvoiceReminders telah di-dispatch ke queue.');
    }
}
