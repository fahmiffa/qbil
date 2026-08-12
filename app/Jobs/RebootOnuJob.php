<?php

namespace App\Jobs;

use App\Models\Olt;
use App\Models\Onu;
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
        public readonly int    $oltId,
        public readonly string $onuId,
        public readonly string $onuName,
    ) {}

    /**
     * POST rebootOp to OLT via HTTP Basic Auth + form-data.
     */
    public function handle(): void
    {
        $olt = Olt::find($this->oltId);
        $onu = Onu::find($this->oltId);

        if ($olt) {
            $ip = $olt->ip;
        }
        elseif ($onu) {
            $ip = 'http://'.$onu->olt_id; 
                    
        }
        else
        {
            return;
        }

        $endpoint = $ip . '/goform/setOnu';

        try {
            $response = Http::withBasicAuth($olt->username, $olt->password)
                ->timeout($this->timeout)
                ->asForm()
                ->post($endpoint, [
                    'onuId'        => $this->onuId,
                    'onuName'      => $this->onuName,
                    'onuOperation' => 'rebootOp',
                ]);

            Log::info(
                "RebootOnuJob: ONU {$this->onuId} ({$this->onuName}) " .
                "di OLT [{$olt->name}] — HTTP {$response->status()}"
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
