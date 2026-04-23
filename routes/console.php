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


// Sync IP Pools from Mikrotik. Using --loop and withoutOverlapping ensures 
// it runs continuously every 5 seconds as requested.
Schedule::command('mikrotik:sync-pools --loop')->everyMinute()->withoutOverlapping();

// Sync DHCP Servers from Mikrotik every 5 seconds
Schedule::command('mikrotik:sync-dhcp --loop')->everyMinute()->withoutOverlapping();

// Sync PPP Profiles from Mikrotik every 5 seconds
Schedule::command('mikrotik:sync-ppp-profiles --loop')->everyMinute()->withoutOverlapping();
