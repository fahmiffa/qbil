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

        if ($customers->isEmpty()) {
            return;
        }

        foreach ($customers as $customer) {
            try {
                $invoiceService = new \App\Services\InvoiceService();
                $invoiceService->generateForCustomer($customer, $this->period);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("BulkGenerateInvoicesJob Error for customer {$customer->id}: " . $e->getMessage());
            }
        }
    }
}
