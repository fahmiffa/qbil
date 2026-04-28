<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// // Scheduler hanya sebagai trigger — logika bisnis diproses oleh queue worker
Schedule::command('invoice:generate')->hourly();
Schedule::command('invoice:remind')->hourly();
Schedule::command('billing:check-due')->hourly();


// Sync network resources is now handled on-demand via SyncMikrotikResourcesJob
// triggered when opening the add customer modal in CustomerManager.

// Check Router Connection Status. Handled by Supervisor in Production.
// Schedule::command('router:check-status --loop')->everyMinute()->withoutOverlapping();
