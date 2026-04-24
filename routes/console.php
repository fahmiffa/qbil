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


// Sync IP Pools from Mikrotik. Handled by Supervisor in Production.
// Schedule::command('mikrotik:sync-pools --loop')->everyMinute()->withoutOverlapping();

// Sync DHCP Servers from Mikrotik. Handled by Supervisor in Production.
// Schedule::command('mikrotik:sync-dhcp --loop')->everyMinute()->withoutOverlapping();

// Sync PPP Profiles from Mikrotik. Handled by Supervisor in Production.
// Schedule::command('mikrotik:sync-ppp-profiles --loop')->everyMinute()->withoutOverlapping();

// Check Router Connection Status. Handled by Supervisor in Production.
// Schedule::command('router:check-status --loop')->everyMinute()->withoutOverlapping();
