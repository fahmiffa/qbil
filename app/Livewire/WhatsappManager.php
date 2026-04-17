<?php

namespace App\Livewire;

use Livewire\Component;

class WhatsappManager extends Component
{
    public $deviceId;
    
    public function mount()
    {
        $this->deviceId = auth()->user()->phone;
    }

    public function render()
    {
        return view('livewire.whatsapp-manager')
            ->layout('layouts.app');
    }
}
