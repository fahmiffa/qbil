<?php

namespace App\Livewire;

use App\Models\Transaction;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class FinanceManager extends Component
{
    use WithPagination;

    public $startDate;
    public $endDate;
    
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
        if (in_array($property, ['startDate', 'endDate'])) {
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

    public function render()
    {
        $query = Transaction::where('user_id', auth()->id())
            ->whereDate('transaction_date', '>=', $this->startDate)
            ->whereDate('transaction_date', '<=', $this->endDate);

        // Calculate totals based on filtered date
        $totalIncome = (clone $query)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $query)->where('type', 'expense')->sum('amount');
        $netProfit = $totalIncome - $totalExpense;

        // Fetch paginated data
        $transactions = $query->orderBy('transaction_date', 'desc')->paginate(15);

        return view('livewire.finance-manager', [
            'transactions' => $transactions,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'netProfit' => $netProfit,
        ])->layout('layouts.app');
    }
}
