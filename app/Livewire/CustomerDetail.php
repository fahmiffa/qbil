<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\MethodPayment;
use App\Models\ViewOnu;
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
    public $discount = 0;
    public $notes = '';
    public $payment_date;
    public $selected_months = [];
    public $selected_year;
    public $selected_payment_method = '';
    public $available_methods = [];

    // For printing multiple invoices
    public $selected_invoices = [];
    public $select_all = false;
    public $onus = [];

    public $month_names = [
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember'
    ];

    public function mount(Customer $customer)
    {
        // Pastikan customer milik user yang login
        abort_if($customer->user_id !== auth()->id(), 403);

        $customer->load(['router', 'package', 'asset']);

        $this->customer = $customer;
        $this->payment_date = now()->format('Y-m-d\TH:i');
        $this->selected_year = now()->year;
        $this->amount_per_month = $customer->package->price ?? 0;

        // Fetch related ONU data by trimmed MAC address (remove last octet)
        $this->loadOnuData();
    }

    public function loadOnuData()
    {
        $viewOnu = $this->customer->viewOnu()->with(['onu.statusHistory' => function($q) {
            $q->latest('id')->limit(10);
        }])->first();

        if ($viewOnu && $viewOnu->onu) {
            $onuArr = $viewOnu->toArray();
            $onuArr['status_history'] = $viewOnu->onu->statusHistory->toArray();
            $this->onus = [$onuArr];
        } else {
            $this->onus = [];
        }
    }

    public function refreshOnuStatus()
    {
        $this->loadOnuData();
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
        $this->total_amount = ($this->months_count * $this->amount_per_month) - $this->discount;
        if ($this->total_amount < 0) $this->total_amount = 0;
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

        // Load available payment methods
        $this->available_methods = MethodPayment::where('user_id', auth()->id())->get();
        $this->selected_payment_method = '';

        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetValidation();
        $this->selected_months = [];
        $this->months_count = 1;
        $this->notes = '';
        $this->discount = 0;
        $this->payment_date = now()->format('Y-m-d\TH:i');
        $this->selected_year = now()->year;
        $this->selected_payment_method = '';
        $this->available_methods = [];
    }

    public function storePayment()
    {
        $this->validate([
            'months_count' => 'required|integer|min:1|max:24',
            'amount_per_month' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'notes' => $this->discount > 0 ? 'required' : 'nullable',
        ], [
            'notes.required' => 'Catatan wajib diisi jika ada diskon.'
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
                        'total_amount' => ($this->amount_per_month - ($this->discount / $this->months_count)),
                        'discount' => ($this->discount / $this->months_count),
                        'status' => 'paid',
                        'paid_at' => $this->payment_date,
                        'payment_method' => $this->selected_payment_method ?: null,
                        'notes' => $this->notes ?: null,
                    ]);
                } else {
                    // Generate baru dan langsung lunas tanpa kode unik
                    $invoice = $invoiceService->generateForCustomer($this->customer, $period);

                    if ($invoice) {
                        $invoice->update([
                            'amount' => $this->amount_per_month,
                            'unique_code' => 0,
                            'total_amount' => ($this->amount_per_month - ($this->discount / $this->months_count)),
                            'discount' => ($this->discount / $this->months_count),
                            'status' => 'paid',
                            'paid_at' => $this->payment_date,
                            'payment_method' => $this->selected_payment_method ?: null,
                            'notes' => $this->notes ?: null,
                        ]);
                    }
                }

                // Kirim notifikasi WA (Job) - Mark as automatic/non-manual for verification check
                if ($invoice) {
                    \App\Jobs\SendManualInvoiceWhatsappJob::dispatch($invoice, null, false);
                }
            }
        });

        session()->flash('message', 'Pembayaran berhasil diverifikasi dan notifikasi dikirim.');
        $this->closeModal();
    }

    public function toggleNotify()
    {
        $this->customer->update([
            'wa_notify' => !$this->customer->wa_notify
        ]);
        $this->dispatch('toast', type: 'success', message: 'Preferensi notifikasi berhasil diperbarui.');
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
