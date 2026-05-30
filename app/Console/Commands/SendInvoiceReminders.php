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
        $now = now();
        $currentTime = $now->format('H:i');

        // Cari user yang memiliki setting reminder_1_time atau reminder_2_time cocok dengan waktu sekarang
        $users = \App\Models\User::whereHas('appSetting', function ($query) use ($currentTime) {
            $query->where('reminder_1_time', $currentTime)
                ->orWhere('reminder_2_time', $currentTime);
        })->get();

        if ($users->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            SendInvoiceRemindersJob::dispatch($user->id);
            // $this->info("Job SendInvoiceReminders di-dispatch untuk user: {$user->name}");
        }
    }
}
