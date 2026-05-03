<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class BulkGenerateInvoicesJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $userId,
        public string $period,
        public ?array $customerIds = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $query = \App\Models\Customer::where('user_id', $this->userId)
            ->where('status', 'active')
            ->whereNotNull('package_id')
            ->whereNotNull('due_date')
            ->whereNotNull('phone')
            ->where('phone', '!=', '');

        if (!empty($this->customerIds)) {
            $query->whereIn('id', $this->customerIds);
        }

        $customers = $query->get();
        $total = $customers->count();
        $cacheKey = "invoice_progress_{$this->userId}";

        if ($customers->isEmpty()) {
            \Illuminate\Support\Facades\Cache::put($cacheKey, [
                'current' => 0,
                'total' => 0,
                'status' => 'done'
            ], 60);
            return;
        }

        foreach ($customers as $index => $customer) {
            try {
                $invoiceService = new \App\Services\InvoiceService();
                $invoiceService->generateForCustomer($customer, $this->period);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("BulkGenerateInvoicesJob Error for customer {$customer->id}: " . $e->getMessage());
            }

            // Update progress
            \Illuminate\Support\Facades\Cache::put($cacheKey, [
                'current' => $index + 1,
                'total' => $total,
                'status' => 'processing'
            ], 300);
        }

        // Finalize
        \Illuminate\Support\Facades\Cache::put($cacheKey, [
            'current' => $total,
            'total' => $total,
            'status' => 'done'
        ], 60);
    }
}
