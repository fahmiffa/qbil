<?php

namespace App\Traits;

trait ChecksDemoMode
{
    public function checkDemoMode()
    {
        if (auth()->check() && auth()->user()->role == 2) {
            $this->dispatch('toast', type: 'error', message: 'Fitur tidak tersedia di akun DEMO (View Only).');
            return true;
        }
        return false;
    }
}
