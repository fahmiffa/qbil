<?php

namespace App\Jobs;

use App\Models\Olt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RebootOnuJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 30;
    public int $backoff = 5;

    public function __construct(
        public readonly int|string $oltIdentifier,
        public readonly string $onuId,
        public readonly string $onuName,
    ) {}

    /**
     * POST rebootOp to OLT via HTTP.
     */
    public function handle(): void
    {
        $endpoint = '';
        $username = null;
        $password = null;

        // Mendukung argumen dari DB (ID) maupun langsung IP (String)
        if (is_numeric($this->oltIdentifier)) {
            $olt = Olt::find($this->oltIdentifier);
        } else {
            // Coba cari di DB berdasarkan IP jika ada, agar Basic Auth tetap bisa dipakai
            $olt = Olt::where('ip', 'like', '%' . $this->oltIdentifier . '%')->first();
        }

        if ($olt) {
            $endpoint = $olt->ip . '/goform/setOnu';
            $username = $olt->username;
            $password = $olt->password;
        } else {
            // Fallback: Gunakan identifier langsung sebagai URL tanpa DB
            $baseUrl = str_starts_with($this->oltIdentifier, 'http') ? $this->oltIdentifier : 'http://' . $this->oltIdentifier;
            $endpoint = rtrim($baseUrl, '/') . '/goform/setOnu';
        }

        try {
            $request = Http::timeout($this->timeout)->asForm();
            
            if ($username && $password) {
                $request = $request->withBasicAuth($username, $password);
            }

            $response = $request->post($endpoint, [
                'onuId'        => $this->onuId,
                'onuName'      => $this->onuName,
                'onuOperation' => 'rebootOp',
            ]);

            Log::info(
                "RebootOnuJob: ONU {$this->onuId} ({$this->onuName}) " .
                "di OLT [{$this->oltIdentifier}] — HTTP {$response->status()}"
            );
        } catch (\Exception $e) {
            Log::error("RebootOnuJob: Gagal reboot ONU {$this->onuId} — {$e->getMessage()}");
            throw $e; // trigger retry
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error(
            "RebootOnuJob: Job gagal setelah {$this->tries} percobaan " .
            "— ONU {$this->onuId}: {$e->getMessage()}"
        );
    }
}
