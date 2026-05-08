<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\AppSetting;
use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendRegistrationWhatsappJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $customer;

    /**
     * Create a new job instance.
     */
    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsappService $whatsappService): void
    {
        $customer = $this->customer->fresh(['package', 'user']);
        
        if (!$customer || !$customer->phone) {
            Log::warning("[SendRegistrationWhatsappJob] Customer tidak ditemukan atau nomor telepon kosong.");
            return;
        }

        if (!$customer->wa_notify) {
            Log::info("[SendRegistrationWhatsappJob] Notifikasi WA dinonaktifkan untuk pelanggan: {$customer->name}");
            return;
        }

        $user = $customer->user;
        if (!$user || !$user->hasFeature('whatsapp')) {
            Log::warning("[SendRegistrationWhatsappJob] Fitur WhatsApp tidak aktif untuk user: " . ($user ? $user->id : 'Unknown'));
            return;
        }


        $appSetting = AppSetting::where('user_id', $user->id)->first();
        if (!$appSetting || empty($appSetting->registration_template)) {
            Log::warning("[SendRegistrationWhatsappJob] Template pendaftaran tidak ditemukan untuk user_id: {$user->id}");
            return;
        }

        $templateText = $appSetting->registration_template;
        $amount = $customer->package ? $customer->package->price : 0;

        Log::info("[SendRegistrationWhatsappJob] Mengirim pesan pendaftaran untuk {$customer->name}...");

        // Cari invoice terbaru (yang baru di-generate saat pendaftaran)
        $latestInvoice = \App\Models\Invoice::where('customer_id', $customer->id)
            ->where('status', 'unpaid')
            ->orderBy('created_at', 'desc')
            ->first();

        $publicUrl = $latestInvoice ? route('public.invoice', ['invoice' => $latestInvoice->id]) : '-';

        // Set locale to Indonesian for date formatting
        \Carbon\Carbon::setLocale('id');

        $message = $whatsappService->formatMessage($templateText, [
            'name'           => $customer->name,
            'amount'         => $amount,
            'package_name'   => $customer->package->name ?? '-',
            'address'        => $customer->address ?? '-',
            'username'       => $customer->username ?? '-',
            'password'       => $customer->password ?? '-',
            'id_pelanggan'   => $customer->id_pelanggan ?? '-',
            'public_url'     => $publicUrl,
            'invoice_number' => $latestInvoice ? $latestInvoice->invoice_number : '-',
            'total_amount'   => $latestInvoice ? $latestInvoice->total_amount : $amount,
            'period'         => $latestInvoice ? \Carbon\Carbon::parse($latestInvoice->billing_period)->translatedFormat('F Y') : '-',
            'due_date'       => ($latestInvoice && $latestInvoice->due_date) ? $latestInvoice->due_date->translatedFormat('d F Y') : '-',
            'user_name'      => $user->name,
        ]);

        $success = $whatsappService->sendMessage(
            $user->phone ?? '',
            $customer->phone,
            $message
        );

        if ($success) {
            Log::info("[SendRegistrationWhatsappJob] Sukses mengirim pesan ke {$customer->phone}.");
        } else {
            Log::error("[SendRegistrationWhatsappJob] Gagal mengirim pesan ke {$customer->phone}.");
        }
    }
}
