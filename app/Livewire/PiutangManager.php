<?php

namespace App\Livewire;

use App\Models\Piutang;
use App\Models\ActivityLog;
use Livewire\Component;
use Livewire\WithPagination;

class PiutangManager extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $filterStatus = 'unpaid';

    // Modal state
    public $isOpen = false;
    public $selectedCustomerId;
    public $selectedCustomerName;
    public $availablePiutangs = [];
    public $selectedPiutangIds = [];

    public function render()
    {
        // Query Customer yang memiliki piutang
        $query = \App\Models\Customer::where('user_id', auth()->id())
            ->whereHas('piutangs', function ($q) {
                if ($this->filterStatus) {
                    $q->where('status', $this->filterStatus);
                }
            })
            ->with(['piutangs' => function ($q) {
                if ($this->filterStatus) {
                    $q->where('status', $this->filterStatus);
                }
                $q->orderBy('created_at', 'desc');
            }]);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('id_pelanggan', 'like', '%' . $this->search . '%');
            });
        }

        return view('livewire.piutang-manager', [
            'customers' => $query->paginate($this->perPage)
        ])->layout('layouts.app', ['header' => 'Manajemen Piutang Pelanggan']);
    }

    public function openPaymentModal($customerId)
    {
        $customer = \App\Models\Customer::findOrFail($customerId);
        $this->selectedCustomerId = $customerId;
        $this->selectedCustomerName = $customer->name;

        $this->availablePiutangs = Piutang::where('customer_id', $customerId)
            ->where('status', 'unpaid')
            ->orderBy('billing_period', 'asc')
            ->get()
            ->toArray();

        $this->selectedPiutangIds = array_column($this->availablePiutangs, 'id');
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset(['selectedCustomerId', 'selectedCustomerName', 'availablePiutangs', 'selectedPiutangIds']);
    }

    public function confirmPayment()
    {
        if (empty($this->selectedPiutangIds)) {
            $this->dispatch('toast', type: 'error', message: 'Pilih setidaknya satu periode untuk dilunasi.');
            return;
        }

        $piutangs = Piutang::whereIn('id', $this->selectedPiutangIds)->get();
        $totalPaid = 0;

        foreach ($piutangs as $piutang) {
            $piutang->update([
                'status' => 'paid',
                'paid_at' => now()
            ]);
            $totalPaid++;
        }

        if ($totalPaid > 0) {
            $this->dispatch('toast', type: 'success', message: "$totalPaid periode piutang berhasil dilunasi.");

            ActivityLog::create([
                'user_id' => auth()->id(),
                'title' => 'PELUNASAN PIUTANG',
                'message' => "Melunasi $totalPaid periode piutang untuk pelanggan: " . $this->selectedCustomerName,
                'type' => 'piutang_crud'
            ]);
        }

        $this->closeModal();
    }

    public function markAsPaid($customerId)
    {
        // Tetap ada untuk compatibility jika dipanggil langsung, 
        // tapi sekarang lebih baik lewat openPaymentModal
        $piutangs = Piutang::where('customer_id', $customerId)
            ->where('status', 'unpaid')
            ->get();

        $updatedRows = 0;
        foreach ($piutangs as $piutang) {
            $piutang->update([
                'status' => 'paid',
                'paid_at' => now()
            ]);
            $updatedRows++;
        }

        if ($updatedRows > 0) {
            $this->dispatch('toast', type: 'success', message: 'Seluruh piutang pelanggan ini telah ditandai lunas.');

            $customer = \App\Models\Customer::find($customerId);
            // Log Activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'title' => 'LUNASI PIUTANG',
                'message' => "Melunasi seluruh piutang pelanggan: " . ($customer->name ?? $customerId),
                'type' => 'piutang_crud'
            ]);
        }
    }

    public function delete($id)
    {
        $piutang = Piutang::findOrFail($id);
        $piutangMsg = "Menghapus data piutang periode: {$piutang->billing_period} - Rp. " . number_format($piutang->amount, 0, ',', '.');
        $piutang->delete();
        $this->dispatch('toast', type: 'warning', message: 'Data piutang telah dihapus.');

        // Log Activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'title' => 'HAPUS PIUTANG',
            'message' => $piutangMsg,
            'type' => 'piutang_crud'
        ]);
    }
}
