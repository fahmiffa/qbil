<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncVoucherToMikrotikJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $orderId) {}

    public function handle(): void
    {
        try {
            $order = \App\Models\VoucherOrder::with(['hotspotUsers', 'package', 'user'])->findOrFail($this->orderId);
            $user = $order->user;

            if (!$user->hasFeature('mikrotik')) {
                return;
            }

            $router = \App\Models\Router::where('user_id', $user->id)->first();
            if (!$router || !$router->is_active) {
                return;
            }

            $package = $order->package;
            $profile = $package->mikrotik_profile ?: 'default';
            $mikrotik = \App\Services\MikrotikService::getInstance($router);

            $commentParts = [];
            if ($package->masa_aktif)    $commentParts[] = 'masa_aktif:' . $package->masa_aktif;
            if ($package->valid_duration) $commentParts[] = 'valid:' . $package->valid_duration;
            $comment = !empty($commentParts) ? implode('|', $commentParts) : 'ebilling';

            foreach ($order->hotspotUsers as $v) {
                try {
                    $mikrotik->updateHotspotUser(
                        $v->username,
                        $v->username,
                        $v->password,
                        $v->profile,
                        $comment,
                        $package->limit_time ?? ''
                    );
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("[SyncVoucherToMikrotikJob] Error syncing voucher {$v->username}: " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("[SyncVoucherToMikrotikJob] Fatal error: " . $e->getMessage());
        }
    }
}
