<?php

namespace App\Livewire;

use App\Models\Piutang;
use Livewire\Component;
use Livewire\WithPagination;

class PiutangManager extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $filterStatus = 'unpaid';

    public function render()
    {
        // Query Customer yang memiliki piutang
        $query = \App\Models\Customer::where('user_id', auth()->id())
            ->whereHas('piutangs', function($q) {
                if ($this->filterStatus) {
                    $q->where('status', $this->filterStatus);
                }
            })
            ->with(['piutangs' => function($q) {
                if ($this->filterStatus) {
                    $q->where('status', $this->filterStatus);
                }
                $q->orderBy('created_at', 'desc');
            }]);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('id_pelanggan', 'like', '%' . $this->search . '%');
            });
        }

        return view('livewire.piutang-manager', [
            'customers' => $query->paginate($this->perPage)
        ])->layout('layouts.app', ['header' => 'Manajemen Piutang Pelanggan']);
    }

    public function markAsPaid($customerId)
    {
        $updatedRows = Piutang::where('customer_id', $customerId)
            ->where('status', 'unpaid')
            ->update([
                'status' => 'paid',
                'paid_at' => now()
            ]);

        if ($updatedRows > 0) {
            $this->dispatch('toast', type: 'success', message: 'Seluruh piutang pelanggan ini telah ditandai lunas.');
        }
    }

    public function delete($id)
    {
        Piutang::findOrFail($id)->delete();
        $this->dispatch('toast', type: 'warning', message: 'Data piutang telah dihapus.');
    }
}
