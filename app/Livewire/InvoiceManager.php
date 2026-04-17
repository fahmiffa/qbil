<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\AppSetting;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class InvoiceManager extends Component
{
    use WithPagination;

    public $search = '';
    public $filter_status = '';
    public $billing_period = '';
    public $perPage = 10;

    protected $queryString = ['search', 'filter_status', 'billing_period', 'perPage'];



    public function mount()
    {
        $this->billing_period = now()->format('Y-m');
    }

    public function generateInvoices()
    {
        $currentPeriod = $this->billing_period;
        $customers = Customer::where('user_id', auth()->id())
            ->where('status', 'active')
            ->whereNotNull('package_id')
            ->get();

        $generatedCount = 0;
        $errorCount = 0;

        foreach ($customers as $customer) {
            // Check if invoice already exists for this period
            $exists = Invoice::where('customer_id', $customer->id)
                ->where('billing_period', $currentPeriod)
                ->exists();

            if ($exists) {
                continue;
            }

            try {
                DB::transaction(function () use ($customer, $currentPeriod, &$generatedCount) {
                    $amount = $customer->package->price ?? 0;
                    
                    // Logic to find unique code (1-999)
                    // We look for a code not used by any 'unpaid' invoice for this user in this period
                    // Note: In a high concurrency environment, this should be more robust
                    $usedCodes = Invoice::whereHas('customer', function($q) {
                            $q->where('user_id', auth()->id());
                        })
                        ->where('status', 'unpaid')
                        ->pluck('unique_code')
                        ->toArray();

                    $availableCodes = array_diff(range(1, 999), $usedCodes);

                    if (empty($availableCodes)) {
                        throw new \Exception("Semua kode unik (1-999) sudah terpakai.");
                    }

                    $uniqueCode = $availableCodes[array_rand($availableCodes)];

                    $totalAmount = $amount + $uniqueCode;
                    
                    // Invoice Number: INV-YYYYMM-XXXX
                    $lastInvoice = Invoice::where('invoice_number', 'like', 'INV-' . str_replace('-', '', $currentPeriod) . '-%')
                        ->orderBy('invoice_number', 'desc')
                        ->first();
                    
                    $seq = 1;
                    if ($lastInvoice) {
                        $parts = explode('-', $lastInvoice->invoice_number);
                        $seq = ((int) end($parts)) + 1;
                    }
                    $invoiceNumber = 'INV-' . str_replace('-', '', $currentPeriod) . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);

                    Invoice::create([
                        'id' => (string) Str::uuid(),
                        'customer_id' => $customer->id,
                        'package_id' => $customer->package_id,
                        'invoice_number' => $invoiceNumber,
                        'amount' => $amount,
                        'unique_code' => $uniqueCode,
                        'total_amount' => $totalAmount,
                        'billing_period' => $currentPeriod,
                        'status' => 'unpaid',
                        'due_date' => now()->addDays(7)->format('Y-m-d'), // Default 7 days
                    ]);

                    $generatedCount++;
                });
            } catch (\Exception $e) {
                $errorCount++;
                session()->flash('error', $e->getMessage());
                break;
            }
        }

        if ($generatedCount > 0) {
            $this->dispatch('toast', type: 'success', message: "$generatedCount Invoice berhasil dibuat.");
        } else if ($errorCount == 0) {
            $this->dispatch('toast', type: 'warning', message: "Tidak ada invoice baru yang perlu dibuat.");
        }

        
        $this->resetPage();
    }

    public function markAsPaid($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        $this->dispatch('toast', type: 'success', message: "Invoice {$invoice->invoice_number} ditandai sebagai LUNAS.");
    }

    public function cancelInvoice($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $invoice->update(['status' => 'canceled']);
        $this->dispatch('toast', type: 'warning', message: "Invoice {$invoice->invoice_number} telah dibatalkan.");
    }

    public function regenerateInvoice($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        
        if ($invoice->status !== 'canceled') {
            return;
        }

        try {
            DB::transaction(function () use ($invoice) {
                // Find a fresh unique code
                $usedCodes = Invoice::whereHas('customer', function($q) {
                        $q->where('user_id', auth()->id());
                    })
                    ->where('status', 'unpaid')
                    ->pluck('unique_code')
                    ->toArray();

                $availableCodes = array_diff(range(1, 999), $usedCodes);

                if (empty($availableCodes)) {
                    throw new \Exception("Kode unik penuh.");
                }

                $uniqueCode = $availableCodes[array_rand($availableCodes)];

                $invoice->update([
                    'status' => 'unpaid',
                    'unique_code' => $uniqueCode,
                    'total_amount' => $invoice->amount + $uniqueCode,
                    'due_date' => now()->addDays(7)->format('Y-m-d'),
                    'package_id' => $invoice->customer->package_id,
                ]);
            });

            $this->dispatch('toast', type: 'success', message: "Invoice {$invoice->invoice_number} berhasil di-generate ulang.");
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function render()
    {
        $query = Invoice::whereHas('customer', function($q) {
                $q->where('user_id', auth()->id());
            })
            ->with(['customer.package']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('invoice_number', 'like', '%' . $this->search . '%')
                  ->orWhere('unique_code', 'like', '%' . $this->search . '%')
                  ->orWhereHas('customer', function($c) {
                      $c->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('username', 'like', '%' . $this->search . '%')
                        ->orWhere('id_pelanggan', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->filter_status) {
            $query->where('status', $this->filter_status);
        }

        if ($this->billing_period) {
            $query->where('billing_period', $this->billing_period);
        }

        $totalCount = $query->count();
        $limit = $this->perPage === 'all' ? max(1, $totalCount) : (int) $this->perPage;
        
        $invoices = $query->orderBy('created_at', 'desc')->paginate($limit);


        return view('livewire.invoice-manager', compact('invoices'))
            ->layout('layouts.app', ['header' => 'Daftar Invoice']);
    }
}
