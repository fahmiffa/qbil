<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\WhatsappService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendInvoiceRemindersJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan ulang jika job gagal.
     * Set 1 karena retry bisa menyebabkan WA reminder terkirim 2x ke pelanggan.
     */
    public int $tries = 1;

    /**
     * Timeout job dalam detik.
     */
    public int $timeout = 120;

    /**
     * Unique key per hari — job reminder tidak bisa masuk queue dua kali
     * dalam satu hari yang sama.
     */
    public function uniqueId(): string
    {
        return 'send-reminders-' . now()->format('Y-m-d');
    }

    /**
     * Durasi lock unique dalam detik (5 menit).
     */
    public function uniqueFor(): int
    {
        return 300;
    }

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

            // Tambahkan link tagihan pabrik (Public Invoice URL) di akhir pesan
            $publicUrl = url("/i/{$invoice->id}");
            $message .= "\n\n📄 *Rincian Tagihan & Bayar Online:*\n" . $publicUrl;

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
