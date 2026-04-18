<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\WhatsappService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendInvoiceRemindersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan ulang jika job gagal.
     */
    public int $tries = 3;

    /**
     * Timeout job dalam detik.
     */
    public int $timeout = 120;

    /**
     * Execute the job.
     */
    public function handle(WhatsappService $whatsappService): void
    {
        Log::info('[SendInvoiceRemindersJob] Mulai pengecekan pengingat tagihan...');

        $invoices = Invoice::with(['customer.user', 'customer.package', 'customer.user.appSetting'])
            ->where('status', 'unpaid')
            ->get();

        $sentCount = 0;

        foreach ($invoices as $invoice) {
            $customer   = $invoice->customer;
            $user       = $customer->user;
            $appSetting = $user->appSetting;

            if (!$appSetting || !$appSetting->notif || !$appSetting->template) {
                continue;
            }

            if (!$customer->due_date) {
                continue;
            }

            // Tanggal pengingat = due_date customer - NOTIF hari
            $dueDate      = Carbon::parse($customer->due_date);
            $reminderDate = $dueDate->copy()->subDays((int) $appSetting->notif);

            // Kirim hanya jika hari ini adalah tanggal pengingat
            if (!now()->isSameDay($reminderDate)) {
                continue;
            }

            Log::info("[SendInvoiceRemindersJob] Mengirim pengingat untuk {$customer->name}...");

            $message = $whatsappService->formatMessage($appSetting->template, [
                'name'           => $customer->name,
                'invoice_number' => $invoice->invoice_number,
                'amount'         => $invoice->amount,
                'unique_code'    => $invoice->unique_code,
                'total_amount'   => $invoice->total_amount,
                'period'         => $invoice->billing_period,
                'due_date'       => $invoice->due_date->format('d-m-Y'),
                'package'        => $customer->package->name ?? '-',
            ]);

            $success = $whatsappService->sendMessage(
                $user->phone ?? '',
                $customer->phone,
                $message
            );

            if ($success) {
                $sentCount++;
            }
        }

        Log::info("[SendInvoiceRemindersJob] Selesai. Total pengingat dikirim: {$sentCount}");
    }
}
