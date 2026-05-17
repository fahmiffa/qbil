<?php

namespace App\Livewire;

use Livewire\Component;

class WhatsappManager extends Component
{
    public $deviceId;
    public $socketUrl;
    public $serverName;
    
    public function mount()
    {
        $user = auth()->user()->load('whatsappServer');
        $this->deviceId = $user->phone;
        
        $this->socketUrl = $user->whatsappServer->socket_url ?? 'https://broadcast.qlabcode.com';
        $this->serverName = $user->whatsappServer->name ?? 'Server Utama (Default)';
    }

    public function render()
    {
        return view('livewire.whatsapp-manager')
            ->layout('layouts.app');
    }
}
