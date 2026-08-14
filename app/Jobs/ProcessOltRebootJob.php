<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Olt;
use App\Services\OltSseService;
use Illuminate\Support\Facades\Log;

class ProcessOltRebootJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $olt;
    public $timeout = 120; // Allow 2 minutes since fetch can take up to 60s

    /**
     * Create a new job instance.
     */
    public function __construct(Olt $olt)
    {
        $this->olt = $olt;
    }

    /**
     * Execute the job.
     */
    public function handle(OltSseService $oltSseService): void
    {
        Log::info("ProcessOltRebootJob: Memproses OLT {$this->olt->name}");
        try {
            $data = $oltSseService->fetchOnuData($this->olt->id);
            if ($data['success'] && !empty($data['onuList'])) {
                foreach ($data['onuList'] as $onu) {
                    $onuId = $onu[0] ?? '';
                    $onuName = $onu[1] ?? '';
                    
                    if ($onuId) {
                        $oltSseService->rebootOnu($this->olt->id, $onuId, $onuName);
                        Log::info("ProcessOltRebootJob: Dispatched reboot job for ONU {$onuId} ({$onuName})");
                    }
                }
            } else {
                Log::warning("ProcessOltRebootJob: Gagal mendapatkan data ONU atau kosong untuk OLT {$this->olt->name}.");
            }
        } catch (\Exception $e) {
            Log::error("ProcessOltRebootJob: Gagal memproses OLT {$this->olt->id}: " . $e->getMessage());
        }
    }
}
