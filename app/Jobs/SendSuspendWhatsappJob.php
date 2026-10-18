<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\AppSetting;
use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSuspendWhatsappJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $invoice;
    protected $overridePhone;
    protected $isManual;

    /**
     * Create a new job instance.
     */
    public function __construct(Invoice $invoice, $overridePhone = null, bool $isManual = false)
    {
        $this->invoice = $invoice;
        $this->overridePhone = $overridePhone;
        $this->isManual = $isManual;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsappService $whatsappService): void
    {
        // Refresh invoice
        $invoice = $this->invoice->fresh(['customer.package', 'customer.user']);

        if (!$invoice || !$invoice->customer) {
            Log::warning("[SendSuspendWhatsappJob] Invoice atau Customer tidak ditemukan.");
            return;
        }

        $customer = $invoice->customer;
        $user = $customer->user;

        // Skip if notification disabled AND not a manual action
        if (!$this->isManual && !$customer->wa_notify) {
            Log::info("[SendSuspendWhatsappJob] Notifikasi WA dinonaktifkan untuk pelanggan: {$customer->name}");
            return;
        }

        if ($user && !$user->hasFeature('whatsapp')) {
            Log::warning("[SendSuspendWhatsappJob] Fitur WhatsApp tidak aktif untuk user: {$user->id}");
            return;
        }

        $targetPhone = $this->overridePhone ?: $customer->phone;

        if (!$targetPhone) {
            Log::warning("[SendSuspendWhatsappJob] Nomor telepon tidak ada untuk customer: {$customer->name}");
            return;
        }

        $appSetting = AppSetting::where('user_id', $user->id)->first();
        $templateText = $appSetting?->template_suspend;

        if (empty($templateText)) {
            $templateText = "Selamat pagi/siang Yth Bpk./Ibu/Saudara/i {name}. Kami memahami kesibukan Kakak sangat padat. Perlu kami informasikan bahwa tagihan WiFi sebesar ID / No. Pelanggan : *{id_pelanggan}* telah melewati tanggal jatuh tempo. Mohon bantuannya untuk segera melakukan pelunasan agar layanan tetap bisa dinikmati tanpa pemutusan sementara. Jika ada kendala, silakan kabari kami ya. Terima kasih.";
        }

        Log::info("[SendSuspendWhatsappJob] Mengirim pesan suspend untuk {$customer->name} (Invoice: {$invoice->invoice_number})...");

        $publicUrl = route('public.invoice', ['invoice' => $invoice->id]);

        // Set locale to Indonesian for date formatting
        \Carbon\Carbon::setLocale('id');

        $message = $whatsappService->formatMessage($templateText, [
            'name'           => $customer->name,
            'invoice_number' => $invoice->invoice_number,
            'amount'         => $invoice->amount,
            'unique_code'    => $invoice->unique_code,
            'total_amount'   => $invoice->total_amount,
            'period'         => \Carbon\Carbon::parse($invoice->billing_period)->translatedFormat('F Y'),
            'due_date'       => $customer->due_date ? $customer->due_date->translatedFormat('d F Y') : '-',
            'package'        => $customer->package->name ?? '-',
            'id_pelanggan'   => $customer->id_pelanggan ?? '-',
            'address'        => $customer->address ?? '-',
            'package_name'   => $customer->package->name ?? '-',
            'public_url'     => $publicUrl,
            'user_name'      => $user->name,
        ]);

        $success = $whatsappService->sendMessage(
            $user->phone ?? '',
            $targetPhone,
            $message
        );

        if ($success) {
            Log::info("[SendSuspendWhatsappJob] Sukses mengirim pesan suspend ke {$targetPhone}.");
        } else {
            Log::error("[SendSuspendWhatsappJob] Gagal mengirim pesan suspend ke {$targetPhone}.");
        }
    }
}
