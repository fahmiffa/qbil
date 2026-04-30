<?php

namespace App\Livewire\Activities;

use Livewire\Component;

class ActivityLog extends Component
{

    public function render()
    {
        $notifications = auth()->user()->notifications()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.activities.activity-log', [
            'notifications' => $notifications
        ])->layout('layouts.app');
    }
}
