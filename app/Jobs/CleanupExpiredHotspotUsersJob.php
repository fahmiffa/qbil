<?php

namespace App\Jobs;

use App\Models\HotspotUser;
use App\Models\Package;
use App\Models\Router;
use App\Services\MikrotikService;
use Carbon\Carbon;
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



        // =====================================================================
        // TIER 2: Hapus berdasarkan valid_until (masa berlaku voucher sebelum aktivasi)
        // Hanya hapus jika user BELUM PERNAH LOGIN (activated_at masih null)
        // =====================================================================
        $unactivatedExpired = HotspotUser::whereNotNull('valid_until')
            ->where('valid_until', '<=', now())
            ->whereNull('activated_at')
            ->get();

        foreach ($unactivatedExpired as $user) {
            try {
                $router = Router::where('user_id', $user->user_id)->first();
                if ($router) {
                    $mikrotik = MikrotikService::getInstance($router);
                    $mikrotik->removeHotspotUser($user->username);
                }
                $user->delete();
                Log::info("[Cleanup][Tier2] Deleted unactivated voucher: {$user->username} (valid_until: {$user->valid_until})");
            } catch (\Exception $e) {
                Log::error("[Cleanup][Tier2] Failed ({$user->username}): " . $e->getMessage());
            }
        }

        // =====================================================================
        // TIER 3 + ACTIVATOR: Proses user MikroTik berdasarkan comment
        // - Activator: Jika uptime > 0 dan belum ada exp: → catat waktu login & hitung exp
        // - Tier 3: Jika exp: sudah terlewati → hapus
        // =====================================================================
        $routers = Router::whereHas('user', function ($q) {
            $q->whereHas('features', function($f) {
                $f->where('parameter', 'hotspot');
            });
        })->get();

        foreach ($routers as $router) {
            try {
                $mikrotik  = MikrotikService::getInstance($router);
                // Sinkronisasi script on-login (hanya berjalan 1x jika belum ada)
                try {
                    $mikrotik->pushHotspotOnLoginScript();
                } catch (\Exception $e) {
                    Log::warning('[Cleanup] pushHotspotOnLoginScript failed: ' . $e->getMessage());
                }

                $mkUsers   = $mikrotik->getHotspotUsers();
                $mkActives = $mikrotik->getHotspotActiveSessions();

                foreach ($mkUsers as $mkUser) {
                    $username = $mkUser['name'] ?? '';
                    $comment  = $mkUser['comment'] ?? '';
                    $uptime   = $mkUser['uptime'] ?? '';

                    if (empty($username)) continue;

                    // --- TIER 3: Cek "exp:" di comment ---
                    if (str_contains($comment, 'exp:')) {
                        $expStr = $this->extractValue($comment, 'exp');
                        $expTime = null;

                        // Format: exp:2026/05/06-07:00:00
                        try { $expTime = Carbon::createFromFormat('Y/m/d-H:i:s', $expStr); } catch (\Exception) {}
                        // Fallback format: exp:2026-05-06 07:00:00
                        if (!$expTime) {
                            try { $expTime = Carbon::parse($expStr); } catch (\Exception) {}
                        }

                        if ($expTime && now()->gte($expTime)) {
                            $mikrotik->removeHotspotUser($username);
                            HotspotUser::where('user_id', $router->user_id)
                                ->where('username', $username)
                                ->delete();
                            Log::info("[Cleanup][Tier3] Deleted exp-reached user: {$username} (exp: {$expStr})");
                            continue; // Skip ke user berikutnya
                        }
                    }

                    // --- ACTIVATOR: Jika sudah login (uptime > 0), belum ada exp:, tapi ada masa_aktif: ---
                    if (
                        !str_contains($comment, 'exp:') &&
                        str_contains($comment, 'masa_aktif:') &&
                        $this->parseMikrotikTime($uptime) > 0
                    ) {
                        $masaAktifStr = $this->extractValue($comment, 'masa_aktif');
                        $masaAktifSec = $this->parseMikrotikTime($masaAktifStr);

                        if ($masaAktifSec > 0) {
                            $loginTime = null;

                            // 1. Coba ambil dari comment "login:" (ditulis oleh On-Login script)
                            if (str_contains($comment, 'login:')) {
                                $loginStr = $this->extractValue($comment, 'login');
                                $loginStr = ucfirst($loginStr); // may/06/2026 -> May/06/2026
                                try {
                                    $loginTime = \Carbon\Carbon::createFromFormat('M/d/Y-H:i:s', $loginStr);
                                } catch (\Exception $e) {
                                    try {
                                        $loginTime = \Carbon\Carbon::parse($loginStr);
                                    } catch (\Exception $ex) {
                                        $loginTime = null;
                                    }
                                }
                            }

                            // 2. Jika tidak ada dari script, cek session aktif saat ini
                            if (!$loginTime) {
                                $activeSession = collect($mkActives)->firstWhere('user', $username);
                                if ($activeSession && isset($activeSession['uptime'])) {
                                    $uptimeSec = $this->parseMikrotikTime($activeSession['uptime']);
                                    $loginTime = now()->subSeconds($uptimeSec);
                                }
                            }

                            // 3. Fallback: gunakan uptime dari profil user (akumulasi)
                            if (!$loginTime) {
                                $uptimeSec = $this->parseMikrotikTime($uptime);
                                $loginTime = now()->subSeconds($uptimeSec);
                            }

                            if ($loginTime) {
                                $expTime   = $loginTime->copy()->addSeconds($masaAktifSec);

                                // Bangun comment baru: pertahankan field lain, tambahkan exp:
                                $newComment = $comment . '|exp:' . $expTime->format('Y/m/d-H:i:s');
                                $mikrotik->updateHotspotUserComment($username, $newComment);

                                // Catat activated_at di DB
                                HotspotUser::where('user_id', $router->user_id)
                                    ->where('username', $username)
                                    ->update(['activated_at' => $loginTime]);

                                Log::info("[Cleanup][Activator] Set exp for {$username}: " . $expTime->format('Y/m/d-H:i:s') . " (loginTime: " . $loginTime->format('Y-m-d H:i:s') . ")");
                            }
                        }
                    }

                    // --- TIER 2b: Hapus berdasarkan limit-uptime dari MikroTik (paket lama tanpa masa_aktif) ---
                    if (isset($mkUser['limit-uptime']) && !empty($mkUser['limit-uptime'])) {
                        $limitSec = $this->parseMikrotikTime($mkUser['limit-uptime']);
                        $upSec    = $this->parseMikrotikTime($uptime);

                        if ($limitSec > 0 && $upSec >= $limitSec) {
                            $mikrotik->removeHotspotUser($username);
                            HotspotUser::where('user_id', $router->user_id)
                                ->where('username', $username)
                                ->delete();
                            Log::info("[Cleanup][Tier2b] Deleted limit-uptime reached: {$username}");
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("[Cleanup] Router {$router->id} error: " . $e->getMessage());
            }
        }

        Log::info('[CleanupExpiredHotspotUsersJob] Cleanup complete.');
    }

    /**
     * Ekstrak nilai dari format "key:value|key2:value2"
     * Contoh: extractValue("masa_aktif:1d|valid:30d", "masa_aktif") → "1d"
     */
    private function extractValue(string $comment, string $key): string
    {
        // Match key: sampai | atau akhir string
        if (preg_match('/' . preg_quote($key, '/') . ':([^\|]+)/', $comment, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    /**
     * Konversi string durasi MikroTik ke detik.
     * Contoh: "1d" → 86400, "3d 06:16:37" → 281797, "1w" → 604800
     */
    private function parseMikrotikTime(string $timeStr): int
    {
        if (empty($timeStr)) return 0;

        $totalSeconds = 0;

        // Handle format "Xd HH:MM:SS" atau "Xw Xd" dll.
        preg_match_all('/(\d+)([wdhms])/i', $timeStr, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $value = (int) $match[1];
            switch (strtolower($match[2])) {
                case 'w': $totalSeconds += $value * 604800; break;
                case 'd': $totalSeconds += $value * 86400;  break;
                case 'h': $totalSeconds += $value * 3600;   break;
                case 'm': $totalSeconds += $value * 60;     break;
                case 's': $totalSeconds += $value;          break;
            }
        }

        // Handle format HH:MM:SS saja (tanpa d/h/m prefix)
        if ($totalSeconds === 0 && preg_match('/^(\d+):(\d+):(\d+)$/', trim($timeStr), $m)) {
            $totalSeconds = ((int)$m[1] * 3600) + ((int)$m[2] * 60) + (int)$m[3];
        }

        return $totalSeconds;
    }
}
