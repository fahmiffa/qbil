<?php

namespace App\Jobs;

use App\Models\HotspotUser;
use App\Models\Router;
use App\Services\MikrotikService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProvisionHotspotUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public HotspotUser $hotspotUser,
        public string $action = 'create',
        public ?string $oldUsername = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Check if user has mikrotik feature
        $user = \App\Models\User::find($this->hotspotUser->user_id);
        if ($user && !$user->hasFeature('mikrotik')) {
            Log::info("Skipping ProvisionHotspotUserJob because MikroTik feature is disabled.");
            return;
        }

        try {
            $router = Router::where('user_id', $this->hotspotUser->user_id)->first();
            if (!$router) {
                Log::warning("No router found for hotspot user {$this->hotspotUser->id}");
                return;
            }

            $mikrotik = MikrotikService::getInstance($router);

            $limitUptime = '';
            if ($this->hotspotUser->package_id) {
                $package = \App\Models\Package::find($this->hotspotUser->package_id);
                if ($package && $package->limit_time) {
                    $limitUptime = $package->limit_time;
                }
            }

            if ($this->action === 'delete') {
                $mikrotik->removeHotspotUser($this->hotspotUser->username);
            } elseif ($this->action === 'update' && $this->oldUsername) {
                $mikrotik->updateHotspotUser(
                    $this->oldUsername,
                    $this->hotspotUser->username,
                    $this->hotspotUser->password,
                    $this->hotspotUser->profile,
                    'ebilling',
                    $limitUptime
                );
            } else {
                $mikrotik->addHotspotUser(
                    $this->hotspotUser->username,
                    $this->hotspotUser->password,
                    $this->hotspotUser->profile,
                    'ebilling',
                    $limitUptime
                );
            }
        } catch (\Exception $e) {
            Log::error("Hotspot Provisioning Error: " . $e->getMessage());
        }
    }
}
