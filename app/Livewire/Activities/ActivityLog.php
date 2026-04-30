<?php

namespace App\Livewire\Activities;

use Livewire\Component;

class ActivityLog extends Component
{
    use \Livewire\WithPagination;

    public function render()
    {
        $notifications = auth()->user()->notifications()
            ->paginate(20);

        return view('livewire.activities.activity-log', [
            'notifications' => $notifications
        ])->layout('layouts.app');
    }
}
