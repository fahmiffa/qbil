<?php

namespace App\Livewire;

use Livewire\Component;

use App\Models\Asset;
use App\Jobs\BroadcastAssetMessage;

class AssetDetail extends Component
{
    public Asset $asset;
    public $isBroadcastOpen = false;
    public $broadcastMessage = '';

    public function mount(Asset $asset)
    {
        // Ensure the asset belongs to the authenticated user
        if ($asset->user_id !== auth()->id()) {
            abort(403);
        }

        $this->asset = $asset->load('customers');
    }

    public function render()
    {
        return view('livewire.asset-detail')
            ->layout('layouts.app', ['header' => 'Detail Asset: ' . $this->asset->name]);
    }

    public function openBroadcast()
    {
        $this->isBroadcastOpen = true;
        $this->broadcastMessage = "Halo {name},\n\n";
    }

    public function closeBroadcast()
    {
        $this->isBroadcastOpen = false;
        $this->broadcastMessage = '';
    }

    public function sendBroadcast()
    {
        $this->validate([
            'broadcastMessage' => 'required|string|min:5',
        ]);

        if ($this->asset->customers->count() === 0) {
            $this->dispatch('toast', type: 'error', message: 'Tidak ada pelanggan terhubung ke aset ini.');
            return;
        }

        if (!auth()->user()->phone) {
            $this->dispatch('toast', type: 'error', message: 'Nomor WhatsApp pengirim belum diatur di profil Anda.');
            return;
        }

        BroadcastAssetMessage::dispatch($this->asset, $this->broadcastMessage, auth()->user());

        $this->closeBroadcast();
        $this->dispatch('toast', type: 'success', message: 'Broadcast pesan telah dijadwalkan ke antrean.');
    }
}
