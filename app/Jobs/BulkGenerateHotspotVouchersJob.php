<?php

namespace App\Jobs;

use App\Models\HotspotUser;
use App\Models\Router;
use App\Models\Package;
use App\Services\MikrotikService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BulkGenerateHotspotVouchersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Timeout job dalam detik (misal 10 menit untuk jumlah besar)
     */
    public int $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $userId,
        public int $packageId,
        public int $quantity
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $user = \App\Models\User::find($this->userId);
            if ($user && !$user->hasFeature('mikrotik')) {
                Log::warning("[BulkGenerateHotspotVouchersJob] MikroTik feature disabled for user {$this->userId}");
                return;
            }
            $router = Router::where('user_id', $this->userId)->first();
            if (!$router) {
                Log::warning("[BulkGenerateHotspotVouchersJob] No router found for user {$this->userId}");
                return;
            }

            $package = Package::find($this->packageId);
            if (!$package) {
                Log::warning("[BulkGenerateHotspotVouchersJob] Package not found: {$this->packageId}");
                return;
            }

            $profile = $package->mikrotik_profile ?: 'default';
            $mikrotik = new MikrotikService($router);

            Log::info("[BulkGenerateHotspotVouchersJob] Starting generation of {$this->quantity} vouchers for user {$this->userId}");

            for ($i = 0; $i < $this->quantity; $i++) {
                $code = '';
                $isUnique = false;
                
                // Cek keunikan kode di database
                while (!$isUnique) {
                    $code = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 5);
                    $exists = HotspotUser::where('user_id', $this->userId)
                        ->where('username', $code)
                        ->exists();
                    if (!$exists) {
                        $isUnique = true;
                    }
                }
                
                // 1. Save to Database
                $hotspotUser = HotspotUser::create([
                    'user_id'    => $this->userId,
                    'username'   => $code,
                    'password'   => $code,
                    'profile'    => $profile,
                    'package_id' => $this->packageId,
                ]);

                // 2. Provision to Mikrotik
                $mikrotik->addHotspotUser($code, $code, $profile, 'ebilling');
            }

            Log::info("[BulkGenerateHotspotVouchersJob] Successfully generated {$this->quantity} vouchers.");
        } catch (\Exception $e) {
            Log::error("[BulkGenerateHotspotVouchersJob] Error: " . $e->getMessage());
        }
    }
}
