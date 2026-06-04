<?php

namespace App\Jobs;

use App\Models\Router;
use App\Services\MikrotikService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckRouterConnectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $router;

    /**
     * Create a new job instance.
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = $this->router->user;
        if ($user && !$user->hasFeature('mikrotik')) {
            return;
        }

        if (!$this->router->is_active) {
            $this->router->update([
                'connection_status' => 'offline',
                'connection_error' => 'Router dinonaktifkan.',
                'last_checked_at' => now(),
            ]);
            return;
        }

        $startTime = microtime(true);
        $status = 'offline';
        $error = null;
        $ping = null;

        try {
            // Berikan waktu ekstra untuk job ini sebelum dibunuh PHP
            @set_time_limit(20);

            $service = MikrotikService::getInstance($this->router);
            if ($service->checkConnection()) {
                $status = 'online';
                $ping = round((microtime(true) - $startTime) * 1000);
            }
        } catch (\Throwable $e) {
            $rawError = $e->getMessage();

            // Sederhanakan pesan error untuk user
            if (str_contains($rawError, 'socket session') || str_contains($rawError, 'connection attempt failed')) {
                $error = 'Gagal menghubungi router, cek lagi konfigurasinya (IP/Port/Firewall).';
            } elseif (str_contains($rawError, 'invalid user name or password')) {
                $error = 'Username atau Password salah.';
            } else {
                $error = $rawError;
            }

            Log::error("Router Connection Check Failed for {$user->name}: " . $rawError);
        }

        $this->router->update([
            'connection_status' => $status,
            'ping_ms' => $ping,
            'connection_error' => $error,
            'last_checked_at' => now(),
        ]);
    }
}
