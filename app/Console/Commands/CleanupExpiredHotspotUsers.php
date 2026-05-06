<?php

namespace App\Console\Commands;

use App\Jobs\CleanupExpiredHotspotUsersJob;
use Illuminate\Console\Command;

class CleanupExpiredHotspotUsers extends Command
{
    protected $signature = 'hotspot:cleanup-expired';
    protected $description = 'Dispatch job to clean up expired hotspot users from DB and MikroTik';

    public function handle(): void
    {
        CleanupExpiredHotspotUsersJob::dispatch();
        $this->info('CleanupExpiredHotspotUsersJob dispatched to queue.');
    }
}
