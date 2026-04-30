<?php

namespace App\Livewire;

use App\Models\VoucherOrder;
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
