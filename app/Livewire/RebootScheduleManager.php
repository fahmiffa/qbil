<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\RebootSchedule;
use Carbon\Carbon;

class RebootScheduleManager extends Component
{
    public $interval_days;
    public $time;

    protected $rules = [
        'interval_days' => 'required|integer|min:1',
        'time' => 'required',
    ];

    public function addSchedule()
    {
        $this->validate();

        $next_run_date = Carbon::now()->addDays((int)$this->interval_days)->toDateString();

        RebootSchedule::create([
            'user_id' => auth()->id(),
            'interval_days' => $this->interval_days,
            'time' => $this->time,
            'next_run_date' => $next_run_date
        ]);

        $this->reset(['interval_days', 'time']);
        $this->dispatch('toast', type: 'success', message: 'Jadwal Reboot berhasil ditambahkan.');
    }

    public function deleteSchedule($id)
    {
        RebootSchedule::where('user_id', auth()->id())->where('id', $id)->delete();
        $this->dispatch('toast', type: 'success', message: 'Jadwal Reboot berhasil dihapus.');
    }

    public function render()
    {
        $schedules = RebootSchedule::where('user_id', auth()->id())->orderBy('next_run_date')->get();
        return view('livewire.reboot-schedule-manager', [
            'schedules' => $schedules
        ]);
    }
}
