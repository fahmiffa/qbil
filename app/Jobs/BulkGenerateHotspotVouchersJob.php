<?php

namespace App\Jobs;

use App\Models\HotspotUser;
use App\Models\Router;
use App\Models\Package;
use App\Models\VoucherOrder;
use App\Services\MikrotikService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
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
        public int $quantity,
        public ?int $voucherOrderId = null
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
            $package = Package::find($this->packageId);
            if (!$package) {
                Log::warning("[BulkGenerateHotspotVouchersJob] Package not found: {$this->packageId}");
                return;
            }

            // Pilih router: Dari paket (jika ada), fallback ke pertama
            $routerId = $package->router_id;
            if ($routerId) {
                $router = Router::where('id', $routerId)->where('user_id', $this->userId)->first();
            } else {
                $router = Router::where('user_id', $this->userId)->first();
            }

            if (!$router) {
                Log::warning("[BulkGenerateHotspotVouchersJob] No router found for user {$this->userId}");
                return;
            }

            if (!$router->is_active) {
                Log::info("[BulkGenerateHotspotVouchersJob] Router '{$router->name}' (ID: {$router->id}) is disabled. Skipping generation.");
                return;
            }
            if (!$package) {
                Log::warning("[BulkGenerateHotspotVouchersJob] Package not found: {$this->packageId}");
                return;
            }

            $profile = $package->mikrotik_profile ?: 'default';
            $mikrotik = MikrotikService::getInstance($router);

            // Build MikroTik comment dari setting paket
            $commentParts = [];
            if ($package->masa_aktif)    $commentParts[] = 'masa_aktif:' . $package->masa_aktif;
            if ($package->valid_duration) $commentParts[] = 'valid:' . $package->valid_duration;
            $comment = !empty($commentParts) ? implode('|', $commentParts) : 'ebilling';

            // Hitung valid_until dari valid_duration jika ada
            $validUntil = null;
            if ($package->valid_duration) {
                $secs = \App\Models\Package::parseDurationToSeconds($package->valid_duration);
                $validUntil = now()->addSeconds($secs);
            }

            Log::info("[BulkGenerateHotspotVouchersJob] Starting generation of {$this->quantity} vouchers for user {$this->userId}");

            $cacheKey = "voucher_progress_{$this->userId}";

            // Optimization 1: Load existing codes into memory for uniqueness check
            $existingCodes = HotspotUser::where('user_id', $this->userId)
                ->pluck('username')
                ->flip()
                ->toArray();

            $vouchersToInsert = [];
            $now = now();

            for ($i = 0; $i < $this->quantity; $i++) {
                $code = '';
                $isUnique = false;

                // Optimization 2: Unique check in memory
                while (!$isUnique) {
                    $code = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 5);
                    if (!isset($existingCodes[$code])) {
                        $isUnique = true;
                        $existingCodes[$code] = true; // Add to in-memory set
                    }
                }

                // Collect for Bulk Insert
                $vouchersToInsert[] = [
                    'user_id'          => $this->userId,
                    'username'         => $code,
                    'password'         => $code,
                    'profile'          => $profile,
                    'package_id'       => $this->packageId,
                    'voucher_order_id' => $this->voucherOrderId,
                    'valid_until'      => $validUntil,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];

                // Provision to Mikrotik (Still individual due to API limitations)
                try {
                    $mikrotik->addHotspotUser($code, $code, $profile, $comment, $package->limit_time ?? '');
                } catch (\Exception $e) {
                    Log::error("[BulkGenerateHotspotVouchersJob] MikroTik Provisioning Error: " . $e->getMessage());
                }

                // Optimization 3: Throttle Cache update (every 10 vouchers or start/end)
                if (($i + 1) % 10 === 0 || $i === 0 || ($i + 1) === $this->quantity) {
                    Cache::put($cacheKey, [
                        'current' => $i + 1,
                        'total'   => $this->quantity,
                        'status'  => 'processing',
                    ], 300);
                }
            }

            // Optimization 4: Bulk insert database records
            if (!empty($vouchersToInsert)) {
                $chunks = array_chunk($vouchersToInsert, 200); // Chunk to avoid SQL limits if very large
                foreach ($chunks as $chunk) {
                    HotspotUser::insert($chunk);
                }
            }

            // Mark as done
            Cache::put($cacheKey, [
                'current' => $this->quantity,
                'total'   => $this->quantity,
                'status'  => 'done',
            ], 60);

            Log::info("[BulkGenerateHotspotVouchersJob] Successfully generated {$this->quantity} vouchers.");

            // 3. Send WhatsApp Notification if it's an order
            if ($this->voucherOrderId) {
                $order = VoucherOrder::find($this->voucherOrderId);
                if ($order) {
                    SendVoucherOrderWhatsappJob::dispatch($order);
                }
            }
        } catch (\Exception $e) {
            Log::error("[BulkGenerateHotspotVouchersJob] Error: " . $e->getMessage());
        }
    }
}
