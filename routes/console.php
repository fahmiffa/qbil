<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('invoice:generate')->everyMinute();
Schedule::command('invoice:remind')->everyMinute();
Schedule::command('billing:check-due')->everyMinute();
Schedule::command('hotspot:cleanup-expired')->everyMinute();
