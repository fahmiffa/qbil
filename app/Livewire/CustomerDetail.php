<?php

namespace App\Livewire;

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerDetail extends Component
{
    use WithPagination;

    public Customer $customer;
    public $isOpen = false;
    public $months_count = 1;
    public $amount_per_month = 0;
    public $total_amount = 0;
    public $notes = '';
    public $payment_date;
    public $selected_months = [];
    public $selected_year;
    
    // For printing multiple invoices
    public $selected_invoices = [];
    public $select_all = false;

    public $month_names = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];

    public function mount(Customer $customer)
    {
        // Pastikan customer milik user yang login
        abort_if($customer->user_id !== auth()->id(), 403);
        $this->customer = $customer;
        $this->payment_date = now()->format('Y-m-d\TH:i');
        $this->selected_year = now()->year;
        $this->amount_per_month = $customer->package->price ?? 0;
    }

    public function toggleMonth($month)
    {
        $key = $this->selected_year . '-' . $month;
        if (in_array($key, $this->selected_months)) {
            $this->selected_months = array_diff($this->selected_months, [$key]);
        } else {
            $this->selected_months[] = $key;
        }
        sort($this->selected_months);
        $this->months_count = max(1, count($this->selected_months));
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->total_amount = $this->months_count * $this->amount_per_month;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected_invoices = $this->customer->invoices()->pluck('id')->toArray();
        } else {
            $this->selected_invoices = [];
        }
    }

    public function printSelected()
    {
        if (empty($this->selected_invoices)) return;
        
        $ids = implode(',', $this->selected_invoices);
        return redirect()->route('print.invoices.bulk', ['ids' => $ids]);
    }

    public function openModal()
    {
        $this->amount_per_month = $this->customer->package->price ?? 0;
        $this->months_count = max(1, count($this->selected_months));
        $this->calculateTotal();
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetValidation();
        $this->selected_months = [];
        $this->months_count = 1;
        $this->notes = '';
        $this->payment_date = now()->format('Y-m-d\TH:i');
        $this->selected_year = now()->year;
    }

    public function storePayment()
    {
        $this->validate([
            'months_count' => 'required|integer|min:1|max:24',
            'amount_per_month' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
        ]);

        $invoiceService = new \App\Services\InvoiceService();

        \Illuminate\Support\Facades\DB::transaction(function () use ($invoiceService) {
            foreach ($this->selected_months as $monthStr) {
                $period = $monthStr; // Y-m format
                
                $invoice = \App\Models\Invoice::where('customer_id', $this->customer->id)
                    ->where('billing_period', $period)
                    ->first();
                
                if ($invoice) {
                    // Update status lunas tanpa kode unik
                    $invoice->update([
                        'amount' => $this->amount_per_month,
                        'unique_code' => 0,
                        'total_amount' => $this->amount_per_month,
                        'status' => 'paid',
                        'paid_at' => $this->payment_date,
                    ]);
                } else {
                    // Generate baru dan langsung lunas tanpa kode unik
                    $invoice = $invoiceService->generateForCustomer($this->customer, $period);
                    
                    if ($invoice) {
                        $invoice->update([
                            'amount' => $this->amount_per_month,
                            'unique_code' => 0,
                            'total_amount' => $this->amount_per_month,
                            'status' => 'paid',
                            'paid_at' => $this->payment_date,
                        ]);
                    }
                }

                // Kirim notifikasi WA (Job)
                if ($invoice) {
                    \App\Jobs\SendManualInvoiceWhatsappJob::dispatch($invoice);
                }
            }
        });

        session()->flash('message', 'Pembayaran berhasil diverifikasi dan notifikasi dikirim.');
        $this->closeModal();
    }

    public function render()
    {
        $invoices = $this->customer->invoices()
            ->with('package')
            ->latest('created_at')
            ->paginate(15);

        return view('livewire.customer-detail', [
            'invoices' => $invoices,
        ])->layout('layouts.app', ['header' => 'Detail Pelanggan']);
    }
}
