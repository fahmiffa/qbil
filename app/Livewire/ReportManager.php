<?php

namespace App\Livewire;

use App\Models\Transaction;
use App\Models\MethodPayment;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportManager extends Component
{
    use WithPagination;

    public $startDate;
    public $endDate;
    public $filterPaymentMethod = '';
    public $filterServiceType = '';
    public $filterType = 'income';
    public $perPage = 25;

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate   = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function updated($property)
    {
        if (in_array($property, ['startDate', 'endDate', 'filterPaymentMethod', 'filterServiceType', 'filterType', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function resetFilters()
    {
        $this->startDate            = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate              = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->filterPaymentMethod  = '';
        $this->filterServiceType    = '';
        $this->filterType           = 'income';
        $this->resetPage();
    }

    public function setThisMonth()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate   = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
    }

    public function setLastMonth()
    {
        $this->startDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
        $this->endDate   = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
    }

    public function setThisYear()
    {
        $this->startDate = Carbon::now()->startOfYear()->format('Y-m-d');
        $this->endDate   = Carbon::now()->endOfYear()->format('Y-m-d');
        $this->resetPage();
    }

    public function render()
    {
        $userId = auth()->id();

        $baseQuery = Transaction::where('user_id', $userId)
            ->whereDate('transaction_date', '>=', $this->startDate)
            ->whereDate('transaction_date', '<=', $this->endDate)
            ->where('type', $this->filterType);

        if ($this->filterPaymentMethod) {
            if ($this->filterPaymentMethod === 'none') {
                $baseQuery->where(function ($q) {
                    $q->whereNull('payment_method')->orWhere('payment_method', '');
                });
            } else {
                $baseQuery->where('payment_method', $this->filterPaymentMethod);
            }
        }

        if ($this->filterServiceType) {
            $baseQuery->where('service_type', $this->filterServiceType);
        }

        // Summary totals on filtered income data
        $totalIncome = (clone $baseQuery)->sum('amount');
        $countIncome = (clone $baseQuery)->count();

        // Breakdown by category
        $categoryBreakdown = (clone $baseQuery)
            ->select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        // Breakdown by payment method
        $methodBreakdown = (clone $baseQuery)
            ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        // Breakdown by service type
        $serviceBreakdown = (clone $baseQuery)
            ->select('service_type', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('service_type')
            ->orderByDesc('total')
            ->get();

        // Also get total expense for net calculation
        $expenseQuery = Transaction::where('user_id', $userId)
            ->whereDate('transaction_date', '>=', $this->startDate)
            ->whereDate('transaction_date', '<=', $this->endDate)
            ->where('type', 'expense');
        $totalExpense = (clone $expenseQuery)->sum('amount');

        // And total income
        $incomeQuery = Transaction::where('user_id', $userId)
            ->whereDate('transaction_date', '>=', $this->startDate)
            ->whereDate('transaction_date', '<=', $this->endDate)
            ->where('type', 'income');
        $absoluteTotalIncome = (clone $incomeQuery)->sum('amount');

        // Paginated transaction table
        $transactions = (clone $baseQuery)
            ->orderByDesc('transaction_date')
            ->paginate($this->perPage);

        $paymentMethods = MethodPayment::where('user_id', $userId)->get();

        return view('livewire.report-manager', [
            'transactions'      => $transactions,
            'totalIncome'       => $absoluteTotalIncome, // total income absolute
            'totalExpense'      => $totalExpense, // total expense absolute
            'netProfit'         => $absoluteTotalIncome - $totalExpense,
            'countIncome'       => $countIncome,
            'categoryBreakdown' => $categoryBreakdown,
            'methodBreakdown'   => $methodBreakdown,
            'serviceBreakdown'  => $serviceBreakdown,
            'paymentMethods'    => $paymentMethods,
        ])->layout('layouts.app', ['header' => 'Laporan Keuangan']);
    }
}
