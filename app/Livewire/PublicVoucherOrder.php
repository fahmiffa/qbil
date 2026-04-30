<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Package;
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
    public $selectedPackage;

    public function mount($uri)
    {
        $this->uri = $uri;
        $this->user = User::where('uri', $uri)->with('appSetting')->firstOrFail();
        
        $this->packages = Package::where('user_id', $this->user->id)
            ->where('tipe', 'hotspot')
            ->get();
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

    public function calculateTotal()
    {
        if ($this->selectedPackage) {
            $this->total_amount = $this->selectedPackage->price * $this->quantity;
        } else {
            $this->total_amount = 0;
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
                $this->qris_payload = QrisLogic::generateDynamicQris(
                    $appSetting->qr, 
                    $this->total_amount
                );
                $this->showCheckout = true;
            } catch (\Exception $e) {
                session()->flash('error', 'Gagal membuat kode pembayaran: ' . $e->getMessage());
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
