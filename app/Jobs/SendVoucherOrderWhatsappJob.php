<?php

namespace App\Jobs;

use App\Models\VoucherOrder;
use App\Models\AppSetting;
use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendVoucherOrderWhatsappJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    /**
     * Create a new job instance.
     */
    public function __construct(VoucherOrder $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsappService $whatsappService): void
    {
        $order = $this->order->fresh(['package', 'user.appSetting', 'hotspotUsers']);
        
        if (!$order) {
            Log::warning("[SendVoucherOrderWhatsappJob] Order tidak ditemukan.");
            return;
        }

        $user = $order->user;
        if ($user && !$user->hasFeature('whatsapp')) {
            Log::warning("[SendVoucherOrderWhatsappJob] Fitur WhatsApp tidak aktif untuk user: {$user->id}");
            return;
        }

        $appSetting = $user->appSetting;
        if (!$appSetting) {
            Log::warning("[SendVoucherOrderWhatsappJob] AppSetting tidak ditemukan.");
            return;
        }

        // Format Message
        $voucherList = "";
        foreach ($order->hotspotUsers as $idx => $voucher) {
            $voucherList .= ($idx + 1) . ". User: *" . $voucher->username . "* | Pass: *" . $voucher->password . "*\n";
        }

        $message = "*PEMBAYARAN VOUCHER BERHASIL*\n\n";
        $message .= "Halo! Terima kasih telah melakukan pembelian voucher WiFi.\n\n";
        $message .= "*Detail Pesanan:*\n";
        $message .= "ID Pesanan: " . $order->order_code . "\n";
        $message .= "Paket: " . ($order->package->name ?? 'Hotspot') . "\n";
        $message .= "Jumlah: " . $order->quantity . " Voucher\n";
        $message .= "Total Bayar: Rp " . number_format($order->total_price + $order->unique_amount, 0, ',', '.') . "\n\n";
        
        $message .= "*Detail Voucher Anda:*\n";
        $message .= $voucherList . "\n";
        
        $message .= "Silakan gunakan akun di atas untuk login ke jaringan WiFi kami.\n\n";
        $message .= "Terima kasih!\n";
        $message .= "-- " . $user->name . " --";

        $success = $whatsappService->sendMessage(
            $user->phone ?? '',
            $order->whatsapp,
            $message
        );

        if ($success) {
            Log::info("[SendVoucherOrderWhatsappJob] Sukses mengirim voucher ke {$order->whatsapp}.");
        } else {
            Log::error("[SendVoucherOrderWhatsappJob] Gagal mengirim voucher ke {$order->whatsapp}.");
        }
    }
}
