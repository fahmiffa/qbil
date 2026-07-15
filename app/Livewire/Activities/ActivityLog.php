<?php

namespace App\Livewire\Activities;

use Livewire\Component;

class ActivityLog extends Component
{
    public $filterCategory = '';
    public $filterStartDate = '';
    public $filterEndDate = '';

    public function render()
    {
        $nQuery = auth()->user()->notifications();
        $lQuery = auth()->user()->activityLogs();

        if ($this->filterCategory) {
            $nQuery->where('data', 'like', '%' . $this->filterCategory . '%');
            $lQuery->where('type', 'like', '%' . $this->filterCategory . '%');
        }

        if ($this->filterStartDate) {
            $nQuery->whereDate('created_at', '>=', $this->filterStartDate);
            $lQuery->whereDate('created_at', '>=', $this->filterStartDate);
        }

        if ($this->filterEndDate) {
            $nQuery->whereDate('created_at', '<=', $this->filterEndDate);
            $lQuery->whereDate('created_at', '<=', $this->filterEndDate);
        }

        // Ambil notifikasi
        $notifications = $nQuery
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
        $logs = $lQuery
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
