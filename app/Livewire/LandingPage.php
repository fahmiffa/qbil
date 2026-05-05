<?php

namespace App\Livewire;

use Livewire\Component;

class LandingPage extends Component
{
    public $customerCount = 50;

    public function getEstimatedPriceProperty()
    {
        if ($this->customerCount < 30) {
            return 30000;
        }

        if ($this->customerCount >= 300) {
            return 500000;
        }

        return $this->customerCount * 1000;
    }

    public function render()
    {
        return view('livewire.landing-page')
            ->layout('layouts.guest');
    }
}
