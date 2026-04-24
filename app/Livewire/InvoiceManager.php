<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Piutang;
use App\Models\Deposit;
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
    public $paid_at;

    protected $queryString = ['search', 'filter_status', 'billing_period', 'perPage'];



    public $showVerifyModal = false;
    public $selectedInvoice = null;

    public function mount()
    {
        $this->billing_period = now()->format('Y-m');
    }

    public function generateInvoices()
    {
        $currentPeriod = $this->billing_period;

        // Dispatch job untuk generate bulk
        \App\Jobs\BulkGenerateInvoicesJob::dispatch(auth()->id(), $currentPeriod);

        $this->dispatch('toast', type: 'info', message: "Proses generate invoice untuk periode $currentPeriod telah dimulai di latar belakang.");

        $this->resetPage();
    }

    public function openVerifyModal($invoiceId)
    {
        $this->selectedInvoice = Invoice::with('customer')->findOrFail($invoiceId);
        $this->paid_at = now()->format('Y-m-d\TH:i');
        $this->showVerifyModal = true;
    }

    public function closeVerifyModal()
    {
        $this->showVerifyModal = false;
        $this->selectedInvoice = null;
    }

    public function markAsPaid($invoiceId = null)
    {
        $id = $invoiceId ?? $this->selectedInvoice->id;
        $invoice = Invoice::findOrFail($id);

        DB::transaction(function () use ($invoice) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => $this->paid_at ?? now(),
            ]);

            // If it was already in piutang table, mark it as paid there too
            \App\Models\Piutang::where('invoice_id', $invoice->id)->update(['status' => 'paid']);

            $customer = $invoice->customer;
            $oldStatus = $customer->status;

            // Logika Reset/Advance Due Date (Dynamic Subscription)
            if ($oldStatus === 'suspended') {
                // Jika dari suspend (baru/vaku), reset siklus dari hari ini
                $newDueDate = now()->addMonth();
            } else {
                // Jika pembayaran rutin, majukan 1 bulan dari tgl jatuh tempo sebelumnya
                $newDueDate = ($customer->due_date ? Carbon::parse($customer->due_date) : now())->addMonth();
            }

            $customer->update([
                'status' => 'active',
                'isolated_at' => null,
                'due_date' => $newDueDate,
                'activated_at' => $customer->activated_at ?? now(),
            ]);

            if ($oldStatus === 'suspended') {
                // Sinkronkan ke router (Buka Isolir)
                \App\Jobs\ProvisionCustomerJob::dispatch($customer, 'update', [
                    'status' => $oldStatus
                ]);

                // GENERATE INVOICE SUSULAN (Jika jadwal reguler sudah terlewat)
                $invoiceService = new \App\Services\InvoiceService();
                $invoiceService->generateFollowUpIfOverdue($customer);
            }
        });

        // Dispatch job untuk notifikasi WhatsApp (Kwitansi Pembayaran)
        \App\Jobs\SendManualInvoiceWhatsappJob::dispatch($invoice);

        $this->dispatch('toast', type: 'success', message: "Invoice {$invoice->invoice_number} ditandai sebagai LUNAS.");
        $this->closeVerifyModal();
    }

    public function markAsPiutang()
    {
        $invoice = $this->selectedInvoice;

        DB::transaction(function () use ($invoice) {
            // Create Piutang Record
            Piutang::updateOrCreate([
                'invoice_id' => $invoice->id
            ], [
                'customer_id' => $invoice->customer_id,
                'user_id' => auth()->id(),
                'amount' => $invoice->total_amount,
                'billing_period' => $invoice->billing_period,
                'status' => 'unpaid',
                'notes' => 'Piutang dari invoice ' . $invoice->invoice_number
            ]);

            $invoice->update([
                'status' => 'paid',
                'paid_at' => $this->paid_at ?? now(),
            ]);

            // Re-activate Customer (Open Isolir) even if it's Piutang
            $customer = $invoice->customer;
            $oldStatus = $customer->status;

            // Reset/Advance Due Date even for Piutang
            if ($oldStatus === 'suspended') {
                $newDueDate = now()->addMonth();
            } else {
                $newDueDate = ($customer->due_date ? Carbon::parse($customer->due_date) : now())->addMonth();
            }

            $customer->update([
                'status' => 'active',
                'isolated_at' => null,
                'due_date' => $newDueDate,
                'activated_at' => $customer->activated_at ?? now(),
            ]);

            if ($oldStatus === 'suspended') {
                // Sinkronkan ke router (Buka Isolir)
                \App\Jobs\ProvisionCustomerJob::dispatch($customer, 'update', [
                    'status' => $oldStatus
                ]);
            }
        });

        $this->dispatch('toast', type: 'info', message: "Invoice {$invoice->invoice_number} dimasukkan ke daftar PIUTANG. Internet dibuka.");
        $this->closeVerifyModal();
    }

    public function cancelInvoice($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $invoice->update(['status' => 'canceled']);
        $this->dispatch('toast', type: 'warning', message: "Invoice {$invoice->invoice_number} telah dibatalkan.");
    }

    public function deleteInvoice($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $invoice->delete();
        $this->dispatch('toast', type: 'error', message: "Invoice telah dihapus permanen.");
    }

    public function sendWhatsappNotification($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);

        // Dispatch job ke antrean
        \App\Jobs\SendManualInvoiceWhatsappJob::dispatch($invoice);

        $this->dispatch('toast', type: 'success', message: "Notifikasi WhatsApp untuk Invoice {$invoice->invoice_number} sedang diproses di latar belakang.");
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
                $usedCodes = Invoice::whereHas('customer', function ($q) {
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
                    'due_date' => $invoice->customer->due_date,
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
        $query = Invoice::whereHas('customer', function ($q) {
            $q->where('user_id', auth()->id());
        })
            ->with(['customer.package']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('invoice_number', 'like', '%' . $this->search . '%')
                    ->orWhere('unique_code', 'like', '%' . $this->search . '%')
                    ->orWhereHas('customer', function ($c) {
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
