<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HotspotUser;
use App\Models\Router;
use App\Services\MikrotikService;
use Illuminate\Support\Facades\Log;

class CleanupExpiredHotspotUsers extends Command
{
    protected $signature = 'hotspot:cleanup-expired';
    protected $description = 'Clean up hotspot users that have passed expired_at or reached their limit-uptime';

    public function handle()
    {
        $this->info('Starting Hotspot User Cleanup...');

        // 1. Cleanup based on absolute expired_at date in Database
        $expiredUsers = HotspotUser::whereNotNull('expired_at')
            ->where('expired_at', '<=', now())
            ->get();

        foreach ($expiredUsers as $user) {
            $this->info("Deleting expired user: {$user->username} (Expired: {$user->expired_at})");
            try {
                $router = Router::where('user_id', $user->user_id)->first();
                if ($router) {
                    $mikrotik = MikrotikService::getInstance($router);
                    $mikrotik->removeHotspotUser($user->username);
                }
                $user->delete();
            } catch (\Exception $e) {
                Log::error("Failed to delete expired DB hotspot user ({$user->username}): " . $e->getMessage());
            }
        }

        // 2. Cleanup based on Mikrotik limit-uptime
        $routers = Router::whereHas('user', function($query) {
            $query->whereHas('hotspotUsers');
        })->get();
        foreach ($routers as $router) {
            try {
                $mikrotik = MikrotikService::getInstance($router);
                $mkUsers = $mikrotik->getHotspotUsers();
                
                foreach ($mkUsers as $mkUser) {
                    if (isset($mkUser['limit-uptime']) && isset($mkUser['uptime'])) {
                        $limitSec = $this->parseMikrotikTime($mkUser['limit-uptime']);
                        $upSec = $this->parseMikrotikTime($mkUser['uptime']);
                        
                        if ($limitSec > 0 && $upSec >= $limitSec) {
                            $this->info("Deleting Mikrotik user limit-uptime reached: {$mkUser['name']}");
                            $mikrotik->removeHotspotUser($mkUser['name']);
                            HotspotUser::where('user_id', $router->user_id)->where('username', $mkUser['name'])->delete();
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("Failed to process limit-uptime for router {$router->id}: " . $e->getMessage());
            }
        }

        $this->info('Cleanup complete.');
    }

    private function parseMikrotikTime($timeStr)
    {
        if (empty($timeStr)) return 0;
        
        $totalSeconds = 0;
        preg_match_all('/(\d+)([wdhms])/', $timeStr, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $value = (int) $match[1];
            $unit = $match[2];
            
            switch ($unit) {
                case 'w': $totalSeconds += $value * 604800; break;
                case 'd': $totalSeconds += $value * 86400; break;
                case 'h': $totalSeconds += $value * 3600; break;
                case 'm': $totalSeconds += $value * 60; break;
                case 's': $totalSeconds += $value; break;
            }
        }
        
        return $totalSeconds;
    }
}
