<?php

namespace App\Livewire;

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerDetail extends Component
{
    use WithPagination;

    public Customer $customer;

    public function mount(Customer $customer)
    {
        // Pastikan customer milik user yang login
        abort_if($customer->user_id !== auth()->id(), 403);
        $this->customer = $customer;
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
