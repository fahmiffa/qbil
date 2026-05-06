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

class CleanupExpiredHotspotUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function handle(): void
    {
        Log::info('[CleanupExpiredHotspotUsersJob] Starting cleanup...');

        // 1. Hapus berdasarkan expired_at di Database
        $expiredUsers = HotspotUser::whereNotNull('expired_at')
            ->where('expired_at', '<=', now())
            ->get();

        foreach ($expiredUsers as $user) {
            try {
                $router = Router::where('user_id', $user->user_id)->first();
                if ($router) {
                    $mikrotik = MikrotikService::getInstance($router);
                    $mikrotik->removeHotspotUser($user->username);
                }
                $user->delete();
                Log::info("[CleanupExpiredHotspotUsersJob] Deleted expired user: {$user->username}");
            } catch (\Exception $e) {
                Log::error("[CleanupExpiredHotspotUsersJob] Failed to delete expired user ({$user->username}): " . $e->getMessage());
            }
        }

        // 2. Hapus berdasarkan limit-uptime dari MikroTik
        $routers = Router::whereHas('user', function ($query) {
            $query->whereHas('hotspotUsers');
        })->get();

        foreach ($routers as $router) {
            try {
                $mikrotik = MikrotikService::getInstance($router);
                $mkUsers  = $mikrotik->getHotspotUsers();

                foreach ($mkUsers as $mkUser) {
                    if (isset($mkUser['limit-uptime']) && isset($mkUser['uptime'])) {
                        $limitSec = $this->parseMikrotikTime($mkUser['limit-uptime']);
                        $upSec    = $this->parseMikrotikTime($mkUser['uptime']);

                        if ($limitSec > 0 && $upSec >= $limitSec) {
                            $mikrotik->removeHotspotUser($mkUser['name']);
                            HotspotUser::where('user_id', $router->user_id)
                                ->where('username', $mkUser['name'])
                                ->delete();
                            Log::info("[CleanupExpiredHotspotUsersJob] Deleted limit-uptime reached user: {$mkUser['name']}");
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("[CleanupExpiredHotspotUsersJob] Router {$router->id} error: " . $e->getMessage());
            }
        }

        Log::info('[CleanupExpiredHotspotUsersJob] Cleanup complete.');
    }

    private function parseMikrotikTime(string $timeStr): int
    {
        if (empty($timeStr)) return 0;

        $totalSeconds = 0;
        preg_match_all('/(\d+)([wdhms])/', $timeStr, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $value = (int) $match[1];
            switch ($match[2]) {
                case 'w': $totalSeconds += $value * 604800; break;
                case 'd': $totalSeconds += $value * 86400;  break;
                case 'h': $totalSeconds += $value * 3600;   break;
                case 'm': $totalSeconds += $value * 60;     break;
                case 's': $totalSeconds += $value;          break;
            }
        }

        return $totalSeconds;
    }
}
