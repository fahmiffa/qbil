<?php

namespace App\Livewire;

use App\Models\Invoice;
use App\Services\QrisLogic;
use Livewire\Component;
use Livewire\Attributes\Layout;

class PublicInvoiceView extends Component
{
    public $invoice;
    public $qris_payload;

    public function mount(Invoice $invoice)
    {
        app()->setLocale('id');
        $this->invoice = $invoice->load(['customer.user.appSetting', 'package']);
        
        $appSetting = $this->invoice->customer->user->appSetting;
        if ($appSetting && $appSetting->qr) {
            try {
                $this->qris_payload = QrisLogic::generateDynamicQris(
                    $appSetting->qr, 
                    $this->invoice->total_amount
                );
            } catch (\Exception $e) {
                logger()->error('QRIS Generation Error: ' . $e->getMessage());
            }
        }
    }

    #[Layout('layouts.invoice-layout')]
    public function render()
    {
        return view('livewire.public-invoice-view');
    }
}
