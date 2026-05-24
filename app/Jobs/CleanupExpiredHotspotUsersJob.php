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
        // Log::info('[CleanupExpiredHotspotUsersJob] Starting cleanup...');



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
                if ($router && $router->is_active) {
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
        // TIER 2: Proses berdasarkan Database (Prioritas Aplikasi)
        // =====================================================================
        $routers = Router::whereHas('user', function ($q) {
            $q->whereHas('features', function ($f) {
                $f->where('parameter', 'hotspot');
            });
        })->get();

        foreach ($routers as $router) {
            if (!$router->is_active) continue;

            try {
                $mikrotik = MikrotikService::getInstance($router);

                // Ambil data user dari MikroTik sekali saja untuk dibandingkan
                $mkUsers = collect($mikrotik->getHotspotUsers());

                // Ambil semua user di DB yang terdaftar pada router ini
                $dbUsers = HotspotUser::where('user_id', $router->user_id)
                    ->with('package')
                    ->get();

                foreach ($dbUsers as $dbUser) {
                    // Cari apakah user di DB ini juga ada di MikroTik
                    $mkUser = $mkUsers->firstWhere('name', $dbUser->username);

                    if (!$mkUser) {
                        // User ada di DB tapi tidak ada di MikroTik, abaikan atau bisa ditambahkan logika sync ulang
                        continue;
                    }

                    $uptime    = $mkUser['uptime'] ?? '0s';
                    $uptimeSec = $this->parseMikrotikTime($uptime);

                    $masaAktif = $dbUser->package?->masa_aktif ?? '0s';
                    $masaAktifSec = $this->parseMikrotikTime($masaAktif);

                    // --- ACTIVATOR: Jika sudah login (uptime > 0) tapi belum tercatat activated_at di DB ---
                    if ($uptimeSec > 0 && is_null($dbUser->activated_at)) {
                        if ($masaAktifSec > 0) {
                            $dbUser->update(['activated_at' => now()]);

                            // Update comment di MikroTik untuk transparansi di Winbox
                            $expTime = now()->addSeconds($masaAktifSec);
                            $newComment = "masa_aktif:{$masaAktif}|exp:" . $expTime->format('Y/m/d-H:i:s');
                            $mikrotik->updateHotspotUserComment($dbUser->username, $newComment);

                            Log::info("[Cleanup][Activator] Activated user: {$dbUser->username} (Exp: " . $expTime->toDateTimeString() . ")");
                        }
                    }

                    // --- CLEANUP: Jika sudah aktif, cek apakah sudah melewati masa berlaku ---
                    if (!is_null($dbUser->activated_at)) {
                        $expTime = $dbUser->activated_at->addSeconds($masaAktifSec);

                        if (now()->gte($expTime)) {
                            $mikrotik->removeHotspotUser($dbUser->username);
                            $dbUser->delete();
                            Log::info("[Cleanup][Expired] Deleted user: {$dbUser->username}");
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("[Cleanup] Router {$router->id} error: " . $e->getMessage());
            }
        }

        // Log::info('[CleanupExpiredHotspotUsersJob] Cleanup complete.');
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
                case 'w':
                    $totalSeconds += $value * 604800;
                    break;
                case 'd':
                    $totalSeconds += $value * 86400;
                    break;
                case 'h':
                    $totalSeconds += $value * 3600;
                    break;
                case 'm':
                    $totalSeconds += $value * 60;
                    break;
                case 's':
                    $totalSeconds += $value;
                    break;
            }
        }

        // Handle format HH:MM:SS saja (tanpa d/h/m prefix)
        if ($totalSeconds === 0 && preg_match('/^(\d+):(\d+):(\d+)$/', trim($timeStr), $m)) {
            $totalSeconds = ((int)$m[1] * 3600) + ((int)$m[2] * 60) + (int)$m[3];
        }

        return $totalSeconds;
    }
}
