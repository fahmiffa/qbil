<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\WhatsappService;
use Carbon\Carbon;
use Illuminate\Support\Str;
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
        return 'send-reminders-' . now()->format('Y-m-d-H');
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
        // Log::info('[SendInvoiceRemindersJob] Mulai pengecekan pengingat tagihan...');

        $invoices = Invoice::whereHas('customer', function ($q) {
                $q->where('wa_notify', true);
            })
            ->with(['customer.user', 'customer.package', 'customer.user.appSetting'])
            ->where('status', 'unpaid')
            ->get();

        $now = now();
        $pendingReminders = []; // Grouped by User ID: [userId => [ [phone, message, customer_name], ... ]]

        foreach ($invoices as $invoice) {
            $customer   = $invoice->customer;
            $user       = $customer->user;
            
            if (!$user || !$user->hasFeature('whatsapp') || (!$user->hasFeature('static') && !$user->hasFeature('pppoe'))) {
                continue;
            }

            $appSetting = $user->appSetting;

            if (!$appSetting || !$appSetting->template) {
                continue;
            }

            if (!$invoice->due_date) {
                continue;
            }

            $dueDate = Carbon::parse($invoice->due_date);
            $shouldSend = false;
            $reminderType = null;

            // Logika Notifikasi Pertama
            $r1Date = $dueDate->copy()->addDays((int) $appSetting->reminder_1_days);
            $r1Time = Carbon::parse($appSetting->reminder_1_time)->format('H:i');
            
            if ($now->isSameDay($r1Date) && $now->format('H:i') === $r1Time) {
                if (!$invoice->reminder_1_sent_at || !$invoice->reminder_1_sent_at->isSameDay($now)) {
                    $shouldSend = true;
                    $reminderType = 1;
                }
            }

            // Logika Notifikasi Kedua
            if (!$shouldSend) {
                $r2Date = $dueDate->copy()->addDays((int) $appSetting->reminder_2_days);
                $r2Time = Carbon::parse($appSetting->reminder_2_time)->format('H:i');
                
                if ($now->isSameDay($r2Date) && $now->format('H:i') === $r2Time) {
                    if (!$invoice->reminder_2_sent_at || !$invoice->reminder_2_sent_at->isSameDay($now)) {
                        $shouldSend = true;
                        $reminderType = 2;
                    }
                }
            }

            if (!$shouldSend) {
                continue;
            }

            // Update tracking agar tidak masuk seleksi lagi jika job berjalan ulang
            if ($reminderType === 1) {
                $invoice->update(['reminder_1_sent_at' => now()]);
            } elseif ($reminderType === 2) {
                $invoice->update(['reminder_2_sent_at' => now()]);
            }

            $publicUrl = route('public.invoice', ['invoice' => $invoice->id]);

            // Pilih template
            $selectedTemplate = ($reminderType === 2 && !empty($appSetting->template_2)) 
                ? $appSetting->template_2 
                : $appSetting->template;

            $message = $whatsappService->formatMessage($selectedTemplate, [
                'name'           => $customer->name,
                'invoice_number' => $invoice->invoice_number,
                'amount'         => $invoice->amount,
                'unique_code'    => $invoice->unique_code,
                'total_amount'   => $invoice->total_amount,
                'period'         => \Carbon\Carbon::parse($invoice->billing_period)->translatedFormat('F Y'),
                'due_date'       => $invoice->due_date->translatedFormat('d F Y'),
                'package'        => $customer->package->name ?? '-',
                'id_pelanggan'   => $customer->id_pelanggan ?? '-',
                'address'        => $customer->address ?? '-',
                'package_name'   => $customer->package->name ?? '-',
                'public_url'     => $publicUrl,
                'user_name'      => $user->name,
            ]);

            // Tambahkan ke daftar tunggu (Group by User)
            $pendingReminders[$user->id][] = [
                'sender_phone' => $user->phone ?? '',
                'target_phone' => $customer->phone,
                'message'      => $message,
                'customer_name'=> $customer->name,
                'user'         => $user
            ];

            // Jika pengingat ke-2 dan ada nomor kedua, tambahkan juga
            if ($reminderType === 2 && !empty($customer->phone2)) {
                $pendingReminders[$user->id][] = [
                    'sender_phone' => $user->phone ?? '',
                    'target_phone' => $customer->phone2,
                    'message'      => $message,
                    'customer_name'=> $customer->name . " (WA 2)",
                    'user'         => $user
                ];
            }
        }

        // Proses pengiriman dengan rate limit 20 per jam per user
        $totalDispatched = 0;
        foreach ($pendingReminders as $userId => $reminders) {
            $chunks = array_chunk($reminders, 25);
            
            foreach ($chunks as $chunkIndex => $chunk) {
                $baseDelaySeconds = $chunkIndex * 3600; // Jeda 1 jam (3600 detik) per batch 20 nomor
                
                foreach ($chunk as $msgIndex => $data) {
                    // Delay mikro 10 detik antar nomor dalam satu batch agar lebih aman
                    $individualDelay = $baseDelaySeconds + ($msgIndex * 10);
                    
                    SendWhatsAppMessageJob::dispatch(
                        $data['sender_phone'],
                        $data['target_phone'],
                        $data['message']
                    )->delay(now()->addSeconds($individualDelay));

                    // Notify admin about the scheduled reminder
                    $data['user']->notify(new \App\Notifications\SystemReportNotification(
                        'Pengingat Dijadwalkan',
                        "Pengingat untuk {$data['customer_name']} dijadwalkan kirim dalam " . ($individualDelay / 60) . " menit.",
                        'notif'
                    ));

                    $totalDispatched++;
                }
            }
        }

        // Log::info("[SendInvoiceRemindersJob] Selesai. Total pengingat dijadwalkan: {$totalDispatched}");
    }
}
