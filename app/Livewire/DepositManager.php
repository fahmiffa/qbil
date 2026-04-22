<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Package;
use App\Models\Deposit;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class DepositManager extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $isOpen = false;

    // Form fields
    public $deposit_id;
    public $customer_id;
    public $months_count = 1;
    public $amount_per_month = 0;
    public $total_amount = 0;
    public $notes;
    public $payment_date;
    public $selected_months = [];
    public $selected_year;

    // Const months
    public $month_names = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];

    // Search for customer in modal
    public $customer_search = '';
    public $selected_customer_name = '';

    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'months_count' => 'required|integer|min:1|max:24',
        'amount_per_month' => 'required|numeric|min:0',
        'total_amount' => 'required|numeric|min:0',
        'payment_date' => 'required|date',
    ];

    public function mount()
    {
        $this->payment_date = now()->format('Y-m-d\TH:i');
        $this->selected_year = now()->year;
    }

    public function updatedCustomerSearch()
    {
        // Resets selected customer if searching again
        $this->customer_id = null;
        $this->selected_customer_name = '';
        $this->amount_per_month = 0;
        $this->calculateTotal();
    }

    public function selectCustomer($id, $name, $price)
    {
        $this->customer_id = $id;
        $this->selected_customer_name = $name;
        $this->amount_per_month = $price;
        $this->customer_search = '';
        $this->calculateTotal();
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
        $this->months_count = count($this->selected_months);
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->total_amount = $this->months_count * $this->amount_per_month;
    }

    public function render()
    {
        $query = Deposit::with(['customer', 'package', 'user'])
            ->whereHas('customer', function($q) {
                $q->where('user_id', auth()->id());
            })
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->whereHas('customer', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('id_pelanggan', 'like', '%' . $this->search . '%');
            });
        }

        $deposits = $query->paginate($this->perPage);

        // Searchable customers for the modal
        $customers = [];
        if (strlen($this->customer_search) >= 2) {
            $customers = auth()->user()->customers()
                ->with('package')
                ->where(function($q) {
                    $q->where('name', 'like', '%' . $this->customer_search . '%')
                      ->orWhere('id_pelanggan', 'like', '%' . $this->customer_search . '%');
                })
                ->limit(5)
                ->get();
        }

        return view('livewire.deposit-manager', [
            'deposits' => $deposits,
            'customers_list' => $customers
        ])->layout('layouts.app');
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function edit($id)
    {
        $deposit = Deposit::with('months')->findOrFail($id);
        $this->deposit_id = $deposit->id;
        $this->customer_id = $deposit->customer_id;
        $this->amount_per_month = $deposit->amount_per_month;
        $this->months_count = $deposit->months_count;
        $this->total_amount = $deposit->total_amount;
        $this->notes = $deposit->notes;
        $this->payment_date = $deposit->payment_date->format('Y-m-d\TH:i');
        
        $customer = $deposit->customer;
        $this->selected_customer_name = $customer->name;
        $this->customer_search = $customer->name;

        // Load selected months from relation
        $this->selected_months = $deposit->months->map(function($m) {
            $d = \Carbon\Carbon::parse($m->month);
            return $d->format('Y') . '-' . $d->format('n');
        })->toArray();

        // Fallback for old data without relationship
        if (empty($this->selected_months) && $deposit->start_date && $deposit->end_date) {
            $current = $deposit->start_date->copy()->startOfMonth();
            $end = $deposit->end_date->copy()->startOfMonth();
            while ($current <= $end) {
                $this->selected_months[] = $current->format('Y-n');
                $current->addMonth();
            }
        }

        // Set selected_year to the year of the last month if available
        if (!empty($this->selected_months)) {
            $lastMonth = end($this->selected_months);
            [$year, $month] = explode('-', $lastMonth);
            $this->selected_year = $year;
        }

        $this->calculateTotals();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetValidation();
    }

    private function resetInputFields()
    {
        $this->deposit_id = null;
        $this->customer_id = null;
        $this->months_count = 0;
        $this->amount_per_month = 0;
        $this->total_amount = 0;
        $this->notes = '';
        $this->payment_date = now()->format('Y-m-d\TH:i');
        $this->customer_search = '';
        $this->selected_customer_name = '';
        $this->selected_months = [];
        $this->selected_year = now()->year;
    }

    public function store()
    {
        $this->validate();

        $customer = Customer::findOrFail($this->customer_id);

        $startDate = !empty($this->selected_months) ? min($this->selected_months) . '-01' : null;
        $endDate = !empty($this->selected_months) ? \Carbon\Carbon::parse(max($this->selected_months) . '-01')->endOfMonth()->format('Y-m-d') : null;

        DB::transaction(function () use ($customer, $startDate, $endDate) {
            $deposit = Deposit::updateOrCreate(['id' => $this->deposit_id], [
                'customer_id' => $this->customer_id,
                'package_id' => $customer->package_id,
                'user_id' => auth()->id(),
                'amount_per_month' => $this->amount_per_month,
                'months_count' => $this->months_count,
                'total_amount' => $this->total_amount,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'notes' => $this->notes,
                'payment_date' => $this->payment_date,
                'status' => 'active',
            ]);

            // Save individual months
            $deposit->months()->delete();
            foreach ($this->selected_months as $monthStr) {
                [$year, $month] = explode('-', $monthStr);
                $deposit->months()->create([
                    'month' => sprintf('%04d-%02d-01', $year, $month)
                ]);
            }
        });

        $this->dispatch('toast', type: 'success', message: $this->deposit_id ? 'Deposit berhasil diperbarui.' : 'Deposit berhasil dicatat.');
        
        $this->closeModal();
        $this->resetInputFields();
    }

    public function delete($id)
    {
        Deposit::findOrFail($id)->delete();
        $this->dispatch('toast', type: 'warning', message: 'Deposit telah dihapus.');
    }
}
