<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\ActivityLog;
use App\Models\Piutang;
use App\Models\Deposit;
use App\Models\AppSetting;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Traits\ChecksDemoMode;

class InvoiceManager extends Component
{
    use WithPagination, ChecksDemoMode;

    public $search = '';
    public $filter_status = '';
    public $filter_due_date = '';
    public $filter_service_type = '';
    public $filter_router = '';
    public $billing_period = '';
    public $perPage = 10;
    public $paid_at;

    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    public $showIsolationModal = false;
    public $isAlertDismissed = false;

    protected $queryString = ['search', 'filter_status', 'filter_due_date', 'filter_service_type', 'filter_router', 'billing_period', 'perPage', 'sortField', 'sortDirection'];

    public function updated($property)
    {
        if (in_array($property, ['search', 'filter_status', 'filter_due_date', 'filter_service_type', 'filter_router', 'billing_period', 'perPage'])) {
            $this->resetPage();
        }
    }



    public $showVerifyModal = false;
    public $showGenerateModal = false;
    public $selectedInvoice = null;
    public $customerSearch = '';
    public $selectedCustomers = [];
    public $invoiceProgress = null; // ['current' => x, 'total' => y, 'status' => 'processing|done']
    public $showPhoneSelectionModal = false;
    public $customerPhones = [];
    public $phoneSelectionInvoiceId = null;

    public function mount()
    {
        $this->billing_period = now()->format('Y-m');
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function checkInvoiceProgress()
    {
        $progress = \Illuminate\Support\Facades\Cache::get("invoice_progress_" . auth()->id());

        if ($progress) {
            $this->invoiceProgress = $progress;

            if ($progress['status'] === 'done') {
                // Bersihkan cache & reset progress setelah 2 detik berikutnya
                \Illuminate\Support\Facades\Cache::forget("invoice_progress_" . auth()->id());
                $this->invoiceProgress = null;
                $this->dispatch('toast', type: 'success', message: 'Semua tagihan berhasil di-generate!');
            }
        } else {
            $this->invoiceProgress = null;
        }
    }

    public function openGenerateModal()
    {
        $this->showGenerateModal = true;
        $this->selectedCustomers = [];
        $this->customerSearch = '';
    }

    public function generateInvoices()
    {
        if ($this->checkDemoMode()) return;

        $currentPeriod = $this->billing_period;
        $customerIds = !empty($this->selectedCustomers) ? $this->selectedCustomers : null;

        // Dispatch job untuk generate bulk
        \App\Jobs\BulkGenerateInvoicesJob::dispatch(auth()->id(), $currentPeriod, $customerIds);

        // Inisialisasi progress awal
        $this->invoiceProgress = [
            'current' => 0,
            'total' => $customerIds ? count($customerIds) : Customer::where('user_id', auth()->id())->where('status', 'active')->count(),
            'status' => 'processing'
        ];

        $msg = $customerIds
            ? "Proses generate invoice untuk " . count($customerIds) . " pelanggan terpilih telah dimulai di latar belakang."
            : "Proses generate invoice untuk SEMUA pelanggan telah dimulai di latar belakang.";

        $this->dispatch('toast', type: 'info', message: $msg);

        // Log Activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'title' => 'GENERATE INVOICE',
            'message' => $msg,
            'type' => 'invoice_crud'
        ]);

        $this->showGenerateModal = false;
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

    public function toggleCustomer($customerId)
    {
        if (in_array($customerId, $this->selectedCustomers)) {
            $this->selectedCustomers = array_diff($this->selectedCustomers, [$customerId]);
        } else {
            $this->selectedCustomers[] = $customerId;
        }
        $this->selectedCustomers = array_values($this->selectedCustomers);
    }

    public function markAsPaid($invoiceId = null)
    {
        if ($this->checkDemoMode()) return;

        $id = $invoiceId ?? $this->selectedInvoice->id;
        $invoice = Invoice::findOrFail($id);

        DB::transaction(function () use ($invoice) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => $this->paid_at ?? now(),
            ]);

            // If it was already in piutang table, mark it as paid there too
            $piutangs = \App\Models\Piutang::where('invoice_id', $invoice->id)->get();
            foreach ($piutangs as $piutang) {
                $piutang->update([
                    'status' => 'paid',
                    'paid_at' => $this->paid_at ?? now()
                ]);
            }

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

        // Log Activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'title' => 'VERIFIKASI INVOICE',
            'message' => "Verifikasi pembayaran lunas: {$invoice->invoice_number} ({$invoice->customer->name})",
            'type' => 'invoice_crud'
        ]);

        $this->closeVerifyModal();
    }

    public function markAsPiutang()
    {
        if ($this->checkDemoMode()) return;

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

        // Log Activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'title' => 'INVOICE KE PIUTANG',
            'message' => "Memasukkan invoice {$invoice->invoice_number} ({$invoice->customer->name}) ke daftar piutang",
            'type' => 'invoice_crud'
        ]);

        $this->closeVerifyModal();
    }

    public function cancelInvoice($invoiceId)
    {
        if ($this->checkDemoMode()) return;

        $invoice = Invoice::findOrFail($invoiceId);
        $invoice->update(['status' => 'canceled']);
        $this->dispatch('toast', type: 'warning', message: "Invoice {$invoice->invoice_number} telah dibatalkan.");

        // Log Activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'title' => 'BATALKAN INVOICE',
            'message' => "Membatalkan invoice: {$invoice->invoice_number}",
            'type' => 'invoice_crud'
        ]);
    }

    public function deleteInvoice($invoiceId)
    {
        if ($this->checkDemoMode()) return;

        $invoice = Invoice::findOrFail($invoiceId);
        $invoiceNum = $invoice->invoice_number;
        $invoice->delete();
        $this->dispatch('toast', type: 'error', message: "Invoice telah dihapus permanen.");

        // Log Activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'title' => 'HAPUS INVOICE',
            'message' => "Menghapus invoice secara permanen: {$invoiceNum}",
            'type' => 'invoice_crud'
        ]);
    }

    public function sendWhatsappNotification($invoiceId)
    {
        if ($this->checkDemoMode()) return;

        $invoice = Invoice::with('customer')->findOrFail($invoiceId);
        $customer = $invoice->customer;

        if ($customer->phone && $customer->phone2) {
            $this->customerPhones = [
                'phone' => $customer->phone,
                'phone2' => $customer->phone2
            ];
            $this->phoneSelectionInvoiceId = $invoiceId;
            $this->showPhoneSelectionModal = true;
            return;
        }

        // Dispatch job ke antrean dengan phone default (phone1) - Mark as manual
        \App\Jobs\SendManualInvoiceWhatsappJob::dispatch($invoice, null, true);

        $this->dispatch('toast', type: 'success', message: "Notifikasi WhatsApp untuk Invoice {$invoice->invoice_number} sedang diproses di latar belakang.");
    }

    public function confirmSendWhatsapp($phone)
    {
        $invoice = Invoice::findOrFail($this->phoneSelectionInvoiceId);

        \App\Jobs\SendManualInvoiceWhatsappJob::dispatch($invoice, $phone, true);

        $this->dispatch('toast', type: 'success', message: "Notifikasi WhatsApp untuk Invoice {$invoice->invoice_number} dikirim ke nomor {$phone}.");

        $this->closePhoneSelectionModal();
    }

    public function closePhoneSelectionModal()
    {
        $this->showPhoneSelectionModal = false;
        $this->customerPhones = [];
        $this->phoneSelectionInvoiceId = null;
    }

    public function regenerateInvoice($invoiceId)
    {
        if ($this->checkDemoMode()) return;

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

        if ($this->filter_due_date) {
            $query->whereDay('due_date', $this->filter_due_date);
        }



        if ($this->filter_service_type) {
            $query->whereHas('customer', function ($q) {
                $q->where('service_type', $this->filter_service_type);
            });
        }

        if ($this->filter_router) {
            $query->whereHas('customer', function ($q) {
                $q->where('router_id', $this->filter_router);
            });
        }

        if ($this->billing_period) {
            $query->where('billing_period', $this->billing_period);
        }

        $totalCount = $query->count();
        $limit = $this->perPage === 'all' ? max(1, $totalCount) : (int) $this->perPage;

        $invoices = $query->orderBy($this->sortField, $this->sortDirection)->paginate($limit);


        $modalCustomers = [];
        if ($this->showGenerateModal && strlen($this->customerSearch) >= 2) {
            $modalCustomers = Customer::where('user_id', auth()->id())
                ->where('status', 'active')
                ->whereNotNull('package_id')
                ->whereNotNull('due_date')
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->customerSearch . '%')
                        ->orWhere('id_pelanggan', 'like', '%' . $this->customerSearch . '%')
                        ->orWhere('username', 'like', '%' . $this->customerSearch . '%');
                })
                ->orderBy('name', 'asc')
                ->limit(8)
                ->get();
        }
        // Get selected customer objects for display
        $selectedCustomerObjects = [];
        if (!empty($this->selectedCustomers)) {
            $selectedCustomerObjects = Customer::whereIn('id', $this->selectedCustomers)
                ->orderBy('name', 'asc')
                ->get();
        }

        $settings = AppSetting::where('user_id', auth()->id())->first();
        $customersToIsolate = collect();
        $isolationTimeRemaining = '';
        $isBeforeIsolation = false;

        if ($settings && $settings->isolate_time !== null && $settings->isolate_days !== null) {
            $now = now();
            $isolateTime = Carbon::parse($settings->isolate_time);
            $offsetDays = (int) $settings->isolate_days;

            // 1. Pelanggan yang isolir HARI INI
            $isTodayBefore = $now->format('H:i') < $isolateTime->format('H:i');
            $targetToday = $now->copy()->subDays($offsetDays);

            // 2. Pelanggan yang isolir BESOK (Peringatan H-1)
            $targetTomorrow = $now->copy()->addDay()->subDays($offsetDays);

            $customersToIsolate = Customer::where('user_id', auth()->id())
                ->where('status', 'active')
                ->whereNotNull('due_date')
                ->where(function ($q) use ($targetToday, $targetTomorrow, $isTodayBefore) {
                    if ($isTodayBefore) {
                        $q->whereDate('due_date', '=', $targetToday)
                            ->orWhereDate('due_date', '=', $targetTomorrow);
                    } else {
                        $q->whereDate('due_date', '=', $targetTomorrow);
                    }
                })
                ->whereHas('invoices', function ($query) {
                    $query->where('status', 'unpaid')
                        ->where('billing_period', now()->format('Y-m'));
                })
                ->get();

            if ($customersToIsolate->isNotEmpty()) {
                $isBeforeIsolation = true;
                if ($isTodayBefore && $customersToIsolate->contains('due_date', $targetToday->format('Y-m-d'))) {
                    $diff = $now->diff($isolateTime);
                    $isolationTimeRemaining = $diff->format('%h jam %i menit');
                } else {
                    $nextIsolation = $now->copy()->addDay()->setTimeFrom($isolateTime);
                    $diff = $now->diff($nextIsolation);
                    $isolationTimeRemaining = ($diff->days > 0 ? $diff->format('%d hari %h jam') : $diff->format('%h jam %i menit'));
                }
            }
        }

        $availableServiceTypes = Customer::where('user_id', auth()->id())
            ->whereNotNull('service_type')
            ->where('service_type', '!=', '')
            ->distinct()
            ->pluck('service_type')
            ->toArray();

        $routers = \App\Models\Router::where('user_id', auth()->id())->orderBy('id')->get();

        return view('livewire.invoice-manager', compact('invoices', 'modalCustomers', 'selectedCustomerObjects', 'customersToIsolate', 'isolationTimeRemaining', 'isBeforeIsolation', 'availableServiceTypes', 'routers'))
            ->layout('layouts.app', ['header' => 'Daftar Invoice']);
    }
}
