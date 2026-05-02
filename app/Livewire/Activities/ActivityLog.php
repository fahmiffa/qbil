<?php

namespace App\Livewire\Activities;

use Livewire\Component;

class ActivityLog extends Component
{

    public function render()
    {
        // Ambil notifikasi
        $notifications = auth()->user()->notifications()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($n) {
                return (object)[
                    'id' => $n->id,
                    'title' => $n->data['title'] ?? 'Aktivitas',
                    'message' => $n->data['message'] ?? '',
                    'type' => $n->data['type'] ?? 'system',
                    'created_at' => $n->created_at,
                    'is_notification' => true,
                    'read_at' => $n->read_at
                ];
            });

        // Ambil log aktivitas
        $logs = auth()->user()->activityLogs()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($l) {
                return (object)[
                    'id' => $l->id,
                    'title' => $l->title,
                    'message' => $l->message,
                    'type' => $l->type,
                    'created_at' => $l->created_at,
                    'is_notification' => false,
                    'read_at' => now() // Log selalu dianggap "terbaca"
                ];
            });

        // Gabungkan dan urutkan
        $allActivities = $notifications->concat($logs)->sortByDesc('created_at');

        return view('livewire.activities.activity-log', [
            'activities' => $allActivities
        ])->layout('layouts.app');
    }
}
