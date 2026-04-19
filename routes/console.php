<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduler hanya sebagai trigger — logika bisnis diproses oleh queue worker
Schedule::command('invoice:generate')->monthlyOn(1, '00:00');
Schedule::command('invoice:remind')->dailyAt('08:00');
Schedule::command('billing:check-due')->dailyAt('00:05');
