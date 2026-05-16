<?php

namespace App\Livewire;

use App\Models\VoucherOrder;
use App\Models\ActivityLog;
use App\Models\HotspotUser;
use App\Jobs\BulkGenerateHotspotVouchersJob;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;

class VoucherManager extends Component
{
    use WithPagination;

    public $selectedOrderId = null;

    protected $listeners = ['confirmMarkAsPaid' => 'markAsPaid'];

    public function toggleVouchers($orderId)
    {
        if ($this->selectedOrderId === $orderId) {
            $this->selectedOrderId = null;
        } else {
            $this->selectedOrderId = $orderId;
        }
    }

    public function requestMarkAsPaid($orderId)
    {
        $this->dispatch('swal:confirm', [
            'type' => 'warning',
            'title' => 'Verifikasi Pembayaran?',
            'text' => 'Pastikan dana sudah masuk. Voucher akan otomatis dibuat dan dikirim ke MikroTik.',
            'id' => $orderId,
            'callback' => 'confirmMarkAsPaid'
        ]);
    }

    public function markAsPaid($orderId)
    {
        $order = VoucherOrder::where('user_id', auth()->id())->findOrFail($orderId);

        if ($order->payment_status === 'paid') {
            return;
        }

        $order->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        // Dispatch Job for Generation & MikroTik Sync
        BulkGenerateHotspotVouchersJob::dispatch(
            auth()->id(),
            $order->package_id,
            $order->quantity,
            $order->id
        );

        session()->flash('message', 'Pembayaran sedang diproses. Voucher akan segera muncul.');

        // Log Activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'title' => 'VOUCHER TERBAYAR',
            'message' => "Verifikasi pembayaran voucher: {$order->order_code} (Rp. " . number_format($order->total_amount, 0, ',', '.') . ")",
            'type' => 'hotspot_crud'
        ]);
    }

    public function sendManualWhatsapp($orderId)
    {
        $order = VoucherOrder::where('user_id', auth()->id())
            ->with(['package', 'hotspotUsers', 'user'])
            ->findOrFail($orderId);
        
        if ($order->hotspotUsers->isEmpty()) {
            session()->flash('message', 'Voucher belum digenerate. Silakan tunggu atau verifikasi ulang.');
            return;
        }

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
        $message .= "-- " . ($order->user->name ?? '') . " --";

        $from = $order->user->phone ?? '';
        $to = $order->whatsapp;

        \App\Jobs\SendWhatsAppMessageJob::dispatch($from, $to, $message);

        session()->flash('message', 'Pesan WhatsApp sedang dikirim ke antrean sistem.');
    }

    public function render()
    {
        $orders = VoucherOrder::where('user_id', auth()->id())
            ->with(['package', 'hotspotUsers'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.voucher-manager', [
            'orders' => $orders
        ])->layout('layouts.app');
    }
}
