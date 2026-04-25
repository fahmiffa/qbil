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

        $startTime = microtime(true);
        $status = 'offline';
        $error = null;
        $ping = null;

        try {
            $service = new MikrotikService($this->router);
            if ($service->checkConnection()) {
                $status = 'online';
                $ping = round((microtime(true) - $startTime) * 1000);
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
            Log::error("Router Connection Check Failed for ID {$this->router->id}: " . $error);
        }

        $this->router->update([
            'connection_status' => $status,
            'ping_ms' => $ping,
            'connection_error' => $error,
            'last_checked_at' => now(),
        ]);
    }
}
