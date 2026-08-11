<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RebootSchedule;
use App\Models\Olt;
use App\Jobs\ProcessOltRebootJob;
use Carbon\Carbon;

class RebootOnuCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'olt:reboot-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Jalankan job reboot ONU sesuai jadwal di RebootSchedule';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $currentDate = $now->toDateString();
        $currentTime = $now->format('H:i');

        $schedules = RebootSchedule::where('next_run_date', $currentDate)
            ->get()
            ->filter(function ($schedule) use ($currentTime) {
                $time = Carbon::parse($schedule->time)->format('H:i');
                return $time === $currentTime;
            });

        if ($schedules->isEmpty()) {
            $this->info("Tidak ada jadwal reboot ONU untuk hari {$currentDate} jam {$currentTime}.");
            return;
        }

        foreach ($schedules as $schedule) {
            $olts = Olt::where('user_id', $schedule->user_id)->get();
            
            foreach ($olts as $olt) {
                $this->info("Dispatching reboot job untuk OLT: {$olt->name}");
                ProcessOltRebootJob::dispatch($olt);
            }

            // Update next_run_date
            $schedule->next_run_date = Carbon::parse($schedule->next_run_date)->addDays((int)$schedule->interval_days)->toDateString();
            $schedule->save();
        }
    }
}
