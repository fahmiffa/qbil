<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceService
{
    /**
     * Generate a single invoice for a customer in a specific period.
     * Returns the created invoice or null if already exists.
     */
    public function generateForCustomer(Customer $customer, string $period): ?Invoice
    {
        // 0. Essential data validation
        if (!$customer->package_id || !$customer->due_date || empty($customer->phone)) {
            return null;
        }

        // 1. Check if invoice already exists for this period
        $exists = Invoice::where('customer_id', $customer->id)
            ->where('billing_period', $period)
            ->exists();

        if ($exists) {
            return null;
        }

        return DB::transaction(function () use ($customer, $period) {
            $amount = $customer->package->price ?? 0;

            // 2. UNIQUE CODE GENERATION (STATIC per pelanggan)
            $uniqueCode = (int) $customer->unique_code;

            if (!$uniqueCode) {
                throw new \Exception("Pelanggan {$customer->name} belum memiliki kode unik. Harap set unique_code di data pelanggan.");
            }

            $totalAmount = $amount + $uniqueCode;

            // 3. INVOICE NUMBER SEQUENCE
            $periodSlug = str_replace('-', '', $period);
            $lastInvoice = Invoice::where('invoice_number', 'like', "INV-{$periodSlug}-%")
                ->orderBy('invoice_number', 'desc')
                ->lockForUpdate()
                ->first();

            $seq = 1;
            if ($lastInvoice) {
                $parts = explode('-', $lastInvoice->invoice_number);
                $seq = ((int) end($parts)) + 1;
            }
            $invoiceNumber = "INV-{$periodSlug}-" . str_pad($seq, 4, '0', STR_PAD_LEFT);

            // 4. DUE DATE CALCULATION
            $originalDueDate = Carbon::parse($customer->due_date);
            $invoiceDueDate = Carbon::parse($period . '-' . $originalDueDate->format('d'));

            // 5. STATUS DEFAULT
            $status = 'unpaid';
            $paidAt = null;

            // 6. CREATE
            return Invoice::create([
                'id'             => (string) Str::uuid(),
                'customer_id'    => $customer->id,
                'package_id'     => $customer->package_id,
                'invoice_number' => $invoiceNumber,
                'amount'         => $amount,
                'unique_code'    => $uniqueCode,
                'total_amount'   => $totalAmount,
                'billing_period' => $period,
                'status'         => $status,
                'due_date'       => $invoiceDueDate,
                'paid_at'        => $paidAt,
            ]);
        });
    }

    /**
     * Check and generate a "follow-up" invoice if a customer was activated
     * and missed their scheduled generation for the current primary month.
     */
    public function generateFollowUpIfOverdue(Customer $customer): ?Invoice
    {
        $setting = $customer->user->appSetting;
        if (!$setting) return null;

        $now = now();
        $currentPeriod = $now->format('Y-m');

        // Check if invoice for current month already exists
        $exists = Invoice::where('customer_id', $customer->id)
            ->where('billing_period', $currentPeriod)
            ->exists();

        if ($exists) return null;

        // Calculate when the generation should have happened
        $offsetDays = (int) $setting->invoice_gen_days;
        $originalDueDate = Carbon::parse($customer->due_date);

        // Target generation date for current month
        $scheduledGenDate = Carbon::parse($currentPeriod . '-' . $originalDueDate->format('d'))
            ->addDays($offsetDays);

        // If today is past the scheduled generation date, generate it now
        if ($now->greaterThanOrEqualTo($scheduledGenDate->startOfDay())) {
            return $this->generateForCustomer($customer, $currentPeriod);
        }

        return null;
    }
}
