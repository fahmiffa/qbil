<?php

namespace App\Livewire;

use App\Models\Transaction;
use App\Models\Invoice;
use App\Models\VoucherOrder;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class FinanceManager extends Component
{
    use WithPagination;

    public $startDate;
    public $endDate;
    public $filterType = 'all'; // all, income, expense
    public $filterPaymentMethod = '';
    public $perPage = 20;


    // Form fields for manual entry
    public $isOpen = false;
    public $type = 'expense';
    public $amount;
    public $category;
    public $description;
    public $transaction_date;

    // Categories preset
    public $expenseCategories = [
        'Beli Alat / Kabel',
        'Bayar Internet Pusat',
        'Bayar Listrik',
        'Gaji / Operasional',
        'Lain-lain'
    ];

    public $incomeCategories = [
        'Pemasangan Baru',
        'Donasi / Tip',
        'Lain-lain'
    ];

    public function mount()
    {
        // Default to current month
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->transaction_date = Carbon::now()->format('Y-m-d\TH:i');
    }

    public function updated($property)
    {
        if (in_array($property, ['startDate', 'endDate', 'filterType', 'filterPaymentMethod', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function openModal()
    {
        $this->resetInputFields();
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    private function resetInputFields()
    {
        $this->type = 'expense';
        $this->amount = '';
        $this->category = '';
        $this->description = '';
        $this->transaction_date = Carbon::now()->format('Y-m-d\TH:i');
    }

    public function store()
    {
        $this->validate([
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
            'transaction_date' => 'required|date',
        ]);

        Transaction::create([
            'user_id' => auth()->id(),
            'type' => $this->type,
            'amount' => $this->amount,
            'category' => $this->category,
            'description' => $this->description,
            'transaction_date' => $this->transaction_date,
        ]);

        session()->flash('message', 'Transaksi berhasil dicatat.');
        $this->closeModal();
    }

    public function delete($id)
    {
        $transaction = Transaction::where('id', $id)->where('user_id', auth()->id())->first();
        if ($transaction) {
            $transaction->delete();
            session()->flash('message', 'Transaksi berhasil dihapus.');
        }
    }

    public function syncOldData()
    {
        $userId = auth()->id();
        $syncedCount = 0;

        // 1. Sync Invoices
        $invoices = Invoice::whereHas('customer', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('status', 'paid')->get();

        foreach ($invoices as $invoice) {
            $exists = Transaction::where('reference_type', Invoice::class)
                ->where('reference_id', $invoice->id)
                ->exists();

            // Check if this invoice is in Piutang
            $isPiutang = \App\Models\Piutang::where('invoice_id', $invoice->id)->exists();

            if (!$exists && !$isPiutang) {
                // Determine service_type from customer or package
                $serviceType = null;
                if ($invoice->customer) {
                    $serviceType = strtolower($invoice->customer->service_type ?? 'static');
                } elseif ($invoice->package) {
                    $serviceType = strtolower($invoice->package->tipe ?? '');
                }

                Transaction::create([
                    'user_id'          => $userId,
                    'type'             => 'income',
                    'amount'           => $invoice->total_amount,
                    'category'         => 'Tagihan Bulanan',
                    'description'      => 'Pembayaran tagihan ' . $invoice->billing_period . ' (' . $invoice->invoice_number . ')',
                    'reference_type'   => Invoice::class,
                    'reference_id'     => $invoice->id,
                    'transaction_date' => $invoice->paid_at ?? $invoice->updated_at ?? now(),
                    'payment_method'   => $invoice->payment_method,
                    'service_type'     => $serviceType,
                ]);
                $syncedCount++;
            }
        }

        // 2. Sync Voucher Orders
        $vouchers = VoucherOrder::where('user_id', $userId)
            ->where('payment_status', 'paid')
            ->get();

        foreach ($vouchers as $order) {
            $exists = Transaction::where('reference_type', VoucherOrder::class)
                ->where('reference_id', $order->id)
                ->exists();

            if (!$exists) {
                Transaction::create([
                    'user_id'          => $userId,
                    'type'             => 'income',
                    'amount'           => $order->total_price + $order->unique_amount,
                    'category'         => 'Voucher Hotspot',
                    'description'      => 'Pembelian ' . $order->quantity . ' Voucher (' . $order->order_code . ')',
                    'reference_type'   => VoucherOrder::class,
                    'reference_id'     => $order->id,
                    'transaction_date' => $order->paid_at ?? $order->updated_at ?? now(),
                    'service_type'     => 'hotspot',
                    'payment_method'   => 'DANA',
                ]);
                $syncedCount++;
            }
        }

        // 3. Sync Paid Piutangs
        $paidPiutangs = \App\Models\Piutang::where('user_id', $userId)
            ->where('status', 'paid')
            ->get();

        foreach ($paidPiutangs as $piutang) {
            $exists = Transaction::where('reference_type', \App\Models\Piutang::class)
                ->where('reference_id', $piutang->id)
                ->exists();

            if (!$exists) {
                // Determine service_type from piutang customer
                $serviceType = null;
                if ($piutang->customer) {
                    $serviceType = strtolower($piutang->customer->service_type ?? 'static');
                }

                Transaction::create([
                    'user_id'          => $userId,
                    'type'             => 'income',
                    'amount'           => $piutang->amount,
                    'category'         => 'Pelunasan Piutang',
                    'description'      => 'Pelunasan piutang dari invoice ' . ($piutang->invoice->invoice_number ?? $piutang->billing_period),
                    'reference_type'   => \App\Models\Piutang::class,
                    'reference_id'     => $piutang->id,
                    'transaction_date' => $piutang->paid_at ?? $piutang->updated_at ?? now(),
                    'payment_method'   => $piutang->payment_method,
                    'service_type'     => $serviceType,
                ]);
                $syncedCount++;
            }
        }

        if ($syncedCount > 0) {
            session()->flash('message', "Berhasil menyinkronkan {$syncedCount} data transaksi lama.");
        } else {
            session()->flash('message', 'Semua data transaksi sudah tersinkronisasi. Tidak ada data baru.');
        }
    }

    public function render()
    {
        $query = Transaction::where('user_id', auth()->id())
            ->whereDate('transaction_date', '>=', $this->startDate)
            ->whereDate('transaction_date', '<=', $this->endDate);

        // Calculate totals based on filtered date (before applying type filter for table)
        $totalIncome = (clone $query)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $query)->where('type', 'expense')->sum('amount');
        $netProfit = $totalIncome - $totalExpense;

        // Apply type filter for the table
        if ($this->filterType !== 'all') {
            $query->where('type', $this->filterType);
        }

        if ($this->filterPaymentMethod) {
            $query->where('payment_method', $this->filterPaymentMethod);
        }

        // Determine pagination
        $paginationCount = $this->perPage === 'all' ? ($query->count() > 0 ? $query->count() : 1) : $this->perPage;

        // Fetch paginated data
        $transactions = $query->orderBy('transaction_date', 'desc')->paginate($paginationCount);

        $user_payment_methods = \App\Models\MethodPayment::where('user_id', auth()->id())->get();

        return view('livewire.finance-manager', [
            'transactions' => $transactions,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'netProfit' => $netProfit,
            'user_payment_methods' => $user_payment_methods,
        ])->layout('layouts.app');
    }
}
