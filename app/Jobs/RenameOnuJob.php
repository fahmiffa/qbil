<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RenameOnuJob implements ShouldQueue
{
    use Queueable;

    public $onuId;
    public $onuName;
    public $host;

    /**
     * Create a new job instance.
     */
    public function __construct(string $onuId, string $onuName, string $host = null)
    {
        $this->onuId = $onuId;
        $this->onuName = $onuName;
        $this->host = $host;
    }

    /**
     * Execute the job.
     */
    public function handle(\App\Services\OltApiService $oltApiService): void
    {
        $oltApiService->renameOnu($this->onuId, $this->onuName, $this->host);
    }
}
