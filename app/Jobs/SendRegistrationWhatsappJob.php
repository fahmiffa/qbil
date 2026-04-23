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

        $user = $customer->user;
        if (!$user) {
            Log::warning("[SendRegistrationWhatsappJob] User tidak ditemukan untuk customer: {$customer->name}");
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

        $message = $whatsappService->formatMessage($templateText, [
            'name'           => $customer->name,
            'amount'         => $amount,
            'package_name'   => $customer->package->name ?? '-',
            'address'        => $customer->address ?? '-',
            'username'       => $customer->username ?? '-',
            'password'       => $customer->password ?? '-',
            // Fallback for some common variables if needed
            'id_pelanggan'   => $customer->id_pelanggan ?? '-',
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
