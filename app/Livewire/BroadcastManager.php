<?php

namespace App\Livewire;

use App\Jobs\SendWhatsAppMessageJob;
use App\Models\Customer;
use App\Models\AppSetting;
use Livewire\Component;

class BroadcastManager extends Component
{
    // Search / recipients
    public string $search = '';
    public array $selectedCustomers = [];   // array of customer IDs
    public bool $sendToAll = false;

    // Message
    public string $message = '';

    // Sender from
    public ?string $from = null;

    // Progress tracking
    public bool $isSending = false;
    public int $sentCount = 0;
    public int $totalCount = 0;
    public bool $isDone = false;
    public string $statusMessage = '';

    // Dropdown open state (managed client-side via Alpine, not needed server side)

    public function mount(): void
    {
        $user = auth()->user()->load('whatsappServer');
        $this->from = $user->phone;
    }

    /**
     * Return suggested customers matching the search term (for the select-search dropdown).
     */
    public function getSuggestionsProperty(): \Illuminate\Support\Collection
    {
        if (strlen($this->search) < 1) {
            return collect();
        }

        return Customer::where('user_id', auth()->id())
            ->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%')
                    ->orWhere('id_pelanggan', 'like', '%' . $this->search . '%');
            })
            ->select('id', 'name', 'phone', 'id_pelanggan')
            ->limit(10)
            ->get();
    }

    /**
     * Return the full data for selected customers (for the chips display).
     */
    public function getSelectedCustomerDataProperty(): \Illuminate\Support\Collection
    {
        if (empty($this->selectedCustomers)) {
            return collect();
        }

        return Customer::whereIn('id', $this->selectedCustomers)
            ->select('id', 'name', 'phone', 'id_pelanggan')
            ->get();
    }

    public function addCustomer(int $id): void
    {
        if (!in_array($id, $this->selectedCustomers)) {
            $this->selectedCustomers[] = $id;
        }
        $this->search = '';
    }

    public function removeCustomer(int $id): void
    {
        $this->selectedCustomers = array_values(array_filter(
            $this->selectedCustomers,
            fn($cid) => $cid !== $id
        ));
    }

    public function sendBroadcast(): void
    {
        $this->validate([
            'message' => 'required|min:5',
        ], [
            'message.required' => 'Pesan tidak boleh kosong.',
            'message.min'      => 'Pesan minimal 5 karakter.',
        ]);

        if (empty($this->from)) {
            $this->dispatch('toast', type: 'error', message: 'Nomor pengirim (WA) belum diatur di profil Anda.');
            return;
        }

        // Determine recipients
        if ($this->sendToAll || empty($this->selectedCustomers)) {
            $customers = Customer::where('user_id', auth()->id())
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->select('id', 'phone')
                ->get();
        } else {
            $customers = Customer::whereIn('id', $this->selectedCustomers)
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->select('id', 'phone')
                ->get();
        }

        if ($customers->isEmpty()) {
            $this->dispatch('toast', type: 'warning', message: 'Tidak ada pelanggan dengan nomor WhatsApp.');
            return;
        }

        $this->isSending   = true;
        $this->isDone      = false;
        $this->sentCount   = 0;
        $this->totalCount  = $customers->count();
        $this->statusMessage = 'Mengantre pesan broadcast...';

        $from    = $this->from;
        $message = $this->message;

        foreach ($customers as $customer) {
            SendWhatsAppMessageJob::dispatch($from, $customer->phone, $message);
        }

        $this->sentCount     = $this->totalCount;
        $this->isDone        = true;
        $this->isSending     = false;
        $this->statusMessage = "✅ {$this->totalCount} pesan berhasil diantrekan ke sistem.";

        $this->dispatch('toast', type: 'success', message: "Broadcast diantrekan: {$this->totalCount} pesan.");
    }

    public function resetForm(): void
    {
        $this->message          = '';
        $this->selectedCustomers = [];
        $this->search           = '';
        $this->sendToAll        = false;
        $this->isSending        = false;
        $this->isDone           = false;
        $this->sentCount        = 0;
        $this->totalCount       = 0;
        $this->statusMessage    = '';
    }

    public function render()
    {
        return view('livewire.broadcast-manager', [
            'suggestions'          => $this->suggestions,
            'selectedCustomerData' => $this->selectedCustomerData,
        ])->layout('layouts.app');
    }
}
