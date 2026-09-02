<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\MethodPayment;
use App\Models\ViewOnu;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Onu;
use App\Models\Olt;
use App\Services\OltApiService;

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

    // Real-time ONU data from OLT HTTP API
    public $onuApiData = null;

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

        // Fetch real-time ONU detail from OLT HTTP API
        $this->loadOnuApiData();
    }

    public function loadOnuApiData()
    {
        $onuName = $this->customer->name;

        if (empty($onuName)) {
            $this->onuApiData = null;
            return;
        }

        try {
            $service = app(OltApiService::class);
            $this->onuApiData = $service->fetchOnuByName($onuName);
        } catch (\Exception $e) {
            $this->onuApiData = null;
        }
    }

    public function rebootOnu($oltUrl, $onuId, $onuName)
    {
        if ($oltUrl && $onuId) {
            \App\Jobs\RebootOnuJob::dispatch($oltUrl, $onuId, $onuName);
            $this->dispatch('notify', ['message' => 'Perintah Reboot ONU telah dikirim dan sedang diproses di belakang layar.', 'type' => 'success']);
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
        $paidCount = 0;
        $skippedPeriods = [];

        \Illuminate\Support\Facades\DB::transaction(function () use ($invoiceService, &$paidCount, &$skippedPeriods) {
            foreach ($this->selected_months as $monthStr) {
                $period = $monthStr; // Y-m format

                // Skip jika sudah ada tagihan di table invoice untuk periode ini
                $exists = \App\Models\Invoice::where('customer_id', $this->customer->id)
                    ->where('billing_period', $period)
                    ->exists();

                if ($exists) {
                    $skippedPeriods[] = $period;
                    continue;
                }

                // Generate invoice baru dan langsung tandai lunas
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

                    // Kirim notifikasi WA (Job)
                    \App\Jobs\SendManualInvoiceWhatsappJob::dispatch($invoice, null, false);
                    $paidCount++;
                }
            }

            // Majukan jatuh tempo dan aktifkan pelanggan sesuai jumlah bulan yang dibayar
            if ($paidCount > 0) {
                $customer = $this->customer;
                $oldStatus = $customer->status;
                $paymentDate = \Carbon\Carbon::parse($this->payment_date)->startOfDay();
                $lastDueDate = $customer->due_date ? \Carbon\Carbon::parse($customer->due_date)->startOfDay() : null;

                if ($oldStatus === 'suspended') {
                    if ($lastDueDate && $paymentDate->lessThanOrEqualTo($lastDueDate->copy()->addDay())) {
                        $newDueDate = $lastDueDate->copy()->addMonths($paidCount);
                    } else {
                        $newDueDate = \Carbon\Carbon::parse($this->payment_date)->addMonths($paidCount);
                    }
                } else {
                    $newDueDate = ($customer->due_date ? \Carbon\Carbon::parse($customer->due_date) : \Carbon\Carbon::parse($this->payment_date))->addMonths($paidCount);
                }

                $customer->update([
                    'status' => 'active',
                    'isolated_at' => null,
                    'due_date' => $newDueDate,
                    'activated_at' => $customer->activated_at ?? now(),
                ]);

                // Buka isolir jika sebelumnya suspended
                if ($oldStatus === 'suspended') {
                    \App\Jobs\ProvisionCustomerJob::dispatch($customer, 'update', [
                        'status' => $oldStatus
                    ]);
                }
            }
        });

        // Log Activity
        if ($paidCount > 0) {
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'title' => 'PEMBAYARAN MANUAL',
                'message' => "Pembayaran manual {$paidCount} periode untuk pelanggan: {$this->customer->name}",
                'type' => 'invoice_crud'
            ]);
        }

        $msg = "Pembayaran {$paidCount} periode berhasil diverifikasi.";
        if (!empty($skippedPeriods)) {
            $msg .= ' Dilewati (sudah ada tagihan): ' . implode(', ', $skippedPeriods);
        }

        session()->flash('message', $msg);
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
