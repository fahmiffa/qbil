<?php

namespace App\Livewire\Activities;

use Livewire\Component;

class ActivityLog extends Component
{
    public $start_date;
    public $end_date;
    public $category;

    public function resetFilters()
    {
        $this->reset(['start_date', 'end_date', 'category']);
    }

    public function render()
    {
        // Ambil notifikasi
        $notificationsQuery = auth()->user()->notifications();

        if ($this->start_date) {
            $notificationsQuery->whereDate('created_at', '>=', $this->start_date);
        }
        if ($this->end_date) {
            $notificationsQuery->whereDate('created_at', '<=', $this->end_date);
        }
        if ($this->category) {
            if ($this->category === 'system') {
                $notificationsQuery->where(function ($q) {
                    $q->where('data->type', 'like', '%system%')
                        ->orWhereNull('data->type');
                });
            } else {
                $notificationsQuery->where('data->type', 'like', '%' . $this->category . '%');
            }
        }

        $notifications = $notificationsQuery->orderBy('created_at', 'desc')
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
        $logsQuery = auth()->user()->activityLogs();

        if ($this->start_date) {
            $logsQuery->whereDate('created_at', '>=', $this->start_date);
        }
        if ($this->end_date) {
            $logsQuery->whereDate('created_at', '<=', $this->end_date);
        }
        if ($this->category) {
            if ($this->category === 'system') {
                $logsQuery->where(function ($q) {
                    $q->where('type', 'like', '%system%')
                        ->orWhereNull('type')
                        ->orWhere('type', '');
                });
            } else {
                $logsQuery->where('type', 'like', '%' . $this->category . '%');
            }
        }

        $logs = $logsQuery->orderBy('created_at', 'desc')
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
