<?php

namespace App\Jobs;

use App\Models\Package;
use App\Models\Router;
use App\Services\MikrotikService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProvisionPackageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Package $package,
        public string $action = 'create',
        public ?string $oldProfileName = null,
        public ?array $extraData = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->package->tipe === 'STATIC') {
            return;
        }

        // Check if user has mikrotik feature
        $user = \App\Models\User::find($this->package->user_id);
        if ($user && !$user->hasFeature('mikrotik')) {
            return;
        }

        try {
            $router = Router::where('user_id', $this->package->user_id)->first();
            if (!$router) return;

            $mikrotik = MikrotikService::getInstance($router);
            $rateLimit = $this->package->speed_upload . '/' . $this->package->speed_download;

            if ($this->action === 'delete') {
                $this->deleteProfile($mikrotik);
            } else {
                $this->upsertProfile($mikrotik, $rateLimit);
            }
        } catch (\Exception $e) {
            Log::error("Package Provisioning Error ({$this->package->id}): " . $e->getMessage());
        }
    }

    private function upsertProfile(MikrotikService $mikrotik, string $rateLimit): void
    {
        $tipe = strtoupper($this->package->tipe);

        if ($tipe === 'HOTSPOT') {
            $profiles = $mikrotik->getHotspotProfiles();
            $existing = collect($profiles)->firstWhere('name', $this->oldProfileName ?: $this->package->mikrotik_profile);

            $sharedUsers = $this->extraData['shared_users'] ?? '1';
            $addressPool = $this->extraData['address_pool'] ?? 'none';
            $sessionTimeout = $this->extraData['session_timeout'] ?? '8h';

            if ($existing) {
                $mikrotik->updateHotspotProfileFull($existing['.id'], $this->package->mikrotik_profile, $rateLimit, $sharedUsers, $addressPool, $sessionTimeout);
            } else {
                $mikrotik->addHotspotProfileFull($this->package->mikrotik_profile, $rateLimit, $sharedUsers, $addressPool, $sessionTimeout);
            }
        } else {
            // PPPOE / PPP
            $profiles = $mikrotik->getPppProfiles();
            $existing = collect($profiles)->firstWhere('name', $this->oldProfileName ?: $this->package->mikrotik_profile);

            $localAddress = $this->extraData['local_address'] ?? null;
            $remoteAddress = $this->extraData['remote_address'] ?? null;

            if ($existing) {
                $mikrotik->updatePppProfileFull($existing['.id'], $this->package->mikrotik_profile, $rateLimit, $localAddress, $remoteAddress);
            } else {
                $mikrotik->addPppProfile($this->package->mikrotik_profile, $rateLimit, $localAddress, $remoteAddress);
            }
        }
    }

    private function deleteProfile(MikrotikService $mikrotik): void
    {
        $tipe = strtoupper($this->package->tipe);
        if ($tipe === 'HOTSPOT') {
            $profiles = $mikrotik->getHotspotProfiles();
            $existing = collect($profiles)->firstWhere('name', $this->package->mikrotik_profile);
            if ($existing) {
                $mikrotik->removeHotspotProfile($existing['.id']);
            }
        } else {
            $profiles = $mikrotik->getPppProfiles();
            $existing = collect($profiles)->firstWhere('name', $this->package->mikrotik_profile);
            if ($existing) {
                $mikrotik->removePppProfileById($existing['.id']);
            }
        }
    }
}
