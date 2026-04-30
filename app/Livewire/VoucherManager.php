<?php

namespace App\Livewire;

use Livewire\Component;

class VoucherManager extends Component
{
    public function render()
    {
        return view('livewire.voucher-manager')
            ->layout('layouts.app');
    }
}
