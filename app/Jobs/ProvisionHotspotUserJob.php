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

            if (!$router->is_active) {
                Log::info("ProvisionHotspotUserJob: Router '{$router->name}' is disabled. Skipping provisioning.");
                return;
            }

            $mikrotik = MikrotikService::getInstance($router);

            $limitUptime = '';
            $comment     = 'ebilling';

            if ($this->hotspotUser->package_id) {
                $package = \App\Models\Package::find($this->hotspotUser->package_id);
                if ($package) {
                    if ($package->limit_time) {
                        $limitUptime = $package->limit_time;
                    }
                    // Build comment: masa_aktif:1d|valid:30d
                    $parts = [];
                    if ($package->masa_aktif)    $parts[] = 'masa_aktif:' . $package->masa_aktif;
                    if ($package->valid_duration) $parts[] = 'valid:' . $package->valid_duration;
                    if (!empty($parts)) {
                        $comment = implode('|', $parts);
                    }
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
                    $comment,
                    $limitUptime
                );
            } else {
                $mikrotik->addHotspotUser(
                    $this->hotspotUser->username,
                    $this->hotspotUser->password,
                    $this->hotspotUser->profile,
                    $comment,
                    $limitUptime
                );
            }
        } catch (\Exception $e) {
            Log::error("Hotspot Provisioning Error: " . $e->getMessage());
        }
    }
}
