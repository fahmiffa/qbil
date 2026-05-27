<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Package;
use App\Models\VoucherOrder;
use App\Services\QrisLogic;
use Livewire\Component;
use Livewire\Attributes\Layout;

class PublicVoucherOrder extends Component
{
    public $uri;
    public $user;
    public $packages = [];

    // Form fields
    public $selectedPackageId;
    public $whatsapp;
    public $quantity = 1;

    // Checkout state
    public $showCheckout = false;
    public $qris_payload;
    public $total_amount = 0;
    public $unique_amount = 0;
    public $final_amount = 0;
    public $selectedPackage;
    public $orderCode;

    public function mount($uri)
    {
        $this->uri = $uri;
        $this->user = User::where('uri', $uri)->with('appSetting')->firstOrFail();

        $this->packages = Package::where('user_id', $this->user->id)
            ->where('tipe', 'hotspot')
            ->get();
    }

    public function increment()
    {
        $this->quantity++;
        $this->calculateTotal();
    }

    public function decrement()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
            $this->calculateTotal();
        }
    }

    public function updatedSelectedPackageId($id)
    {
        $this->selectedPackage = $this->packages->find($id);
        $this->calculateTotal();
    }

    public function updatedQuantity()
    {
        if ($this->quantity < 1) $this->quantity = 1;
        $this->calculateTotal();
    }

    public $discount_amount = 0;
    public $applied_discount_name = null;
    public $is_member = false;

    public function updatedWhatsapp()
    {
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        if ($this->selectedPackage) {
            $baseTotal = $this->selectedPackage->price * $this->quantity;

            // Check member status
            $this->is_member = false;
            $this->applied_discount_name = null;
            $this->discount_amount = 0;

            if ($this->whatsapp) {
                // Remove leading '0' or '62' if necessary depending on how they store it, assuming straight match for now
                $this->is_member = \App\Models\Member::where('user_id', $this->user->id)
                    ->where('whatsapp_number', $this->whatsapp)
                    ->exists();
            }

            if ($this->is_member) {
                $discount = \App\Models\Discount::where('user_id', $this->user->id)
                    ->where('package_id', $this->selectedPackageId)
                    ->first();

                if ($discount) {
                    $this->applied_discount_name = $discount->name;

                    if ($discount->type === 'percentage') {
                        $this->discount_amount = $baseTotal * ($discount->amount / 100);
                    } else {
                        // nominal applied per quantity
                        $this->discount_amount = $discount->amount * $this->quantity;
                    }
                }
            }

            $this->total_amount = max(0, $baseTotal - $this->discount_amount);
        } else {
            $this->total_amount = 0;
            $this->discount_amount = 0;
            $this->applied_discount_name = null;
            $this->is_member = false;
        }
    }

    public function checkout()
    {
        $this->validate([
            'selectedPackageId' => 'required',
            'whatsapp' => 'required|numeric|min:10',
            'quantity' => 'required|integer|min:1',
        ], [
            'selectedPackageId.required' => 'Pilih paket terlebih dahulu.',
            'whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp.numeric' => 'Nomor WhatsApp harus berupa angka.',
            'quantity.min' => 'Jumlah minimal adalah 1.',
        ]);

        $this->calculateTotal();

        $appSetting = $this->user->appSetting;
        if ($appSetting && $appSetting->qr) {
            try {
                // Generate Unique Nominal (Random 1-999)
                $this->unique_amount = rand(1, 499); // Keeping it under 500 for fairness
                $this->final_amount = $this->total_amount + $this->unique_amount;

                // Generate Unique Order Code (VCHR-timestamp-random)
                $this->orderCode = 'VCHR-' . strtoupper(substr(uniqid(), -6)) . '-' . date('is');

                // Create Order in Database
                VoucherOrder::create([
                    'order_code' => $this->orderCode,
                    'user_id' => $this->user->id,
                    'package_id' => $this->selectedPackageId,
                    'whatsapp' => $this->whatsapp,
                    'quantity' => $this->quantity,
                    'total_price' => $this->total_amount,
                    'unique_amount' => $this->unique_amount,
                    'payment_status' => 'unpaid',
                ]);

                $this->qris_payload = QrisLogic::generateDynamicQris(
                    $appSetting->qr,
                    $this->final_amount
                );
                $this->showCheckout = true;
            } catch (\Exception $e) {
                session()->flash('error', 'Gagal membuat pesanan: ' . $e->getMessage());
            }
        } else {
            session()->flash('error', 'Metode pembayaran QRIS belum dikonfigurasi oleh penjual.');
        }
    }

    public function back()
    {
        $this->showCheckout = false;
    }

    #[Layout('layouts.invoice-layout')]
    public function render()
    {
        return view('livewire.public-voucher-order');
    }
}
