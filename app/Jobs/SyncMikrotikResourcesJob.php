<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\MikrotikService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncMikrotikResourcesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected User $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $router = $this->user->router;
        
        if (!$router) {
            return;
        }

        if (!$this->user->hasFeature('mikrotik')) {
            return;
        }

        try {
            $mikrotik = MikrotikService::getInstance($router);
            
            // Sync all relevant network resources to local DB
            $mikrotik->syncPoolsToDb();
            $mikrotik->syncDhcpServersToDb();
            $mikrotik->syncPppProfilesToDb();
            
            Log::info("MikroTik resources sync completed for user: {$this->user->name}");
        } catch (\Exception $e) {
            Log::error("Failed to sync MikroTik resources for user {$this->user->name}: " . $e->getMessage());
        }
    }
}
