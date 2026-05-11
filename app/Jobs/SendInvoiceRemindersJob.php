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
use Illuminate\Support\Facades\Cache;

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
        Log::info('[SendInvoiceRemindersJob] Mulai pengecekan pengingat tagihan...');

        $invoices = Invoice::whereHas('customer', function ($q) {
                $q->where('wa_notify', true);
            })
            ->with(['customer.user', 'customer.package', 'customer.user.appSetting'])
            ->where('status', 'unpaid')
            ->get();

        $sentCount = 0;
        $now = now();
        $userSentCounts = [];

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
            $r1Date = $dueDate->copy()->addDays((int) $appSetting->reminder_1_days)->startOfDay();
            $r1Time = Carbon::parse($appSetting->reminder_1_time)->format('H:i');
            
            // Berikan toleransi 7 hari jika sebelumnya terkena limit harian
            if ($now->startOfDay()->between($r1Date, $r1Date->copy()->addDays(7)) && $now->format('H:i') === $r1Time) {
                // Cek apakah belum pernah dikirim
                if (!$invoice->reminder_1_sent_at) {
                    $shouldSend = true;
                    $reminderType = 1;
                }
            }

            // Logika Notifikasi Kedua
            if (!$shouldSend) {
                $r2Date = $dueDate->copy()->addDays((int) $appSetting->reminder_2_days)->startOfDay();
                $r2Time = Carbon::parse($appSetting->reminder_2_time)->format('H:i');
                
                // Berikan toleransi 7 hari jika sebelumnya terkena limit harian
                if ($now->startOfDay()->between($r2Date, $r2Date->copy()->addDays(7)) && $now->format('H:i') === $r2Time) {
                    // Cek apakah belum pernah dikirim
                    if (!$invoice->reminder_2_sent_at) {
                        $shouldSend = true;
                        $reminderType = 2;
                    }
                }
            }

            if (!$shouldSend) {
                continue;
            }

            $senderPhone = $user->phone ?? 'default';
            $cacheKey = 'wa_daily_limit_' . preg_replace('/[^0-9]/', '', $senderPhone) . '_' . $now->format('Y-m-d');
            $currentCount = Cache::get($cacheKey, 0);
            
            // Jika pengingat ke-2 dan ada nomor customer kedua, akan butuh 2 kuota
            $neededQuota = ($reminderType === 2 && !empty($customer->phone2)) ? 2 : 1;

            if ($currentCount + $neededQuota > 30) {
                Log::warning("[SendInvoiceRemindersJob] Limit harian (30) WA tercapai untuk pengirim {$senderPhone}. Mengabaikan pengingat untuk {$customer->name}.");
                continue;
            }

            // Update tracking agar tidak terkirim dua kali
            if ($reminderType === 1) {
                $invoice->update(['reminder_1_sent_at' => now()]);
            } elseif ($reminderType === 2) {
                $invoice->update(['reminder_2_sent_at' => now()]);
            }

            Log::info("[SendInvoiceRemindersJob] Mengirim pengingat untuk {$customer->name}...");

            $publicUrl = route('public.invoice', ['invoice' => $invoice->id]);

            // Pilih template berdasarkan jenis pengingat
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

            // Dispatch individual job with 10-second delay increments to avoid blocking
            SendWhatsAppMessageJob::dispatch(
                $user->phone ?? '',
                $customer->phone,
                $message
            )->delay(now()->addSeconds($sentCount * 10));

            // Jika pengingat ke-2 dan ada nomor kedua, kirim juga ke nomor kedua
            if ($reminderType === 2 && !empty($customer->phone2)) {
                $sentCount++;
                SendWhatsAppMessageJob::dispatch(
                    $user->phone ?? '',
                    $customer->phone2,
                    $message
                )->delay(now()->addSeconds($sentCount * 10));
            }

            // Simpan jumlah yang sudah dikirim hari ini ke Cache (berlaku hingga akhir hari)
            Cache::put($cacheKey, $currentCount + $neededQuota, now()->endOfDay());

            $userId = $user->id;
            if (!isset($userSentCounts[$userId])) {
                $userSentCounts[$userId] = [
                    'user' => $user,
                    'count' => 0,
                ];
            }
            $userSentCounts[$userId]['count']++;

            // Notify admin about each reminder sent
            $user->notify(new \App\Notifications\SystemReportNotification(
                'Pengingat Terkirim',
                "Pengingat tagihan dikirim ke {$customer->name}. Isi pesan: \"" . Str::limit($message, 50) . "\"",
                'notif'
            ));

            $sentCount++;
        }

        Log::info("[SendInvoiceRemindersJob] Selesai. Total pengingat dikirim: {$sentCount}");
    }
}
