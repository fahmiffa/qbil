<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Invoice;
use App\Models\VoucherOrder;
use App\Jobs\BulkGenerateHotspotVouchersJob;
use App\Jobs\RemoveIsolirJob;
use App\Jobs\SendManualInvoiceWhatsappJob;

class PaymentController extends Controller
{
    /**
     * Store/Handle incoming payment notifications from Android App (Webhook).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validasi API Key untuk Keamanan
        $expectedKey = env('PAYMENT_API_KEY', 'welcomeTo26@FFA');
        $authHeader = $request->header('Authorization');

        if (!$authHeader || str_replace('Bearer ', '', $authHeader) !== $expectedKey) {
            Log::channel('payment')->warning('Unauthorized payment push attempt', [
                'ip' => $request->ip(),
                'header' => $authHeader
            ]);
            return response()->json(['message' => 'Unauthorized. Invalid API Key.'], 401);
        }

        // Sample Payload:
        // {"id":273397,"title":"Financial Diary","text":"You spent IDR 85,000.00 at Groceries.","package":"com.bca.mybca.omni.android","postTime":1775903218006}

        $payload = $request->validate([
            'id' => 'required',
            'title' => 'nullable|string',
            'text' => 'required|string',
            'package' => 'required|string',
            'postTime' => 'required'
        ]);

        // Whitelist Package Validation
        $whitelist = [
            'com.bca.mybca.omni.android',
            'id.bmri.livin',
            'com.bca.msb',
            'id.dana',
            'com.shopeepay.id',
        ];

        if (!in_array($payload['package'], $whitelist)) {
            // Log::channel('payment')->info('Notification ignored: package not in whitelist', ['package' => $payload['package']]);
            return response()->json(['message' => 'Source Notification ignored.'], 200);
        }

        // Mapping package to payment method
        $paymentMethod = null;
        if ($payload['package'] === 'com.bca.msb') {
            $paymentMethod = 'BCA';
        } elseif ($payload['package'] === 'id.dana') {
            $paymentMethod = 'DANA';
        }

        Log::channel('payment')->info(json_encode($payload));
        // Extract number from text. Also include title just in case the amount is there
        $textToParse = $payload['title'] . ' ' . $payload['text'];

        // Find all sequences that look like numbers/currency: e.g. 85,000.00 or 150.000 or 150000
        preg_match_all('/(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{1,2})?)/', $textToParse, $matches);

        $verified = false;
        $processedItem = null;

        if (!empty($matches[1])) {
            foreach ($matches[1] as $matchedAmount) {
                // If it ends with .xx or ,xx (like .00 for cents), strip it out
                $cleanStr = preg_replace('/[.,]\d{2}$/', '', $matchedAmount);
                // Remove all non-digit characters to get the raw integer
                $nominal = (int) preg_replace('/\D/', '', $cleanStr);

                // Extract unique code (last 3 digits)
                // Assuming base prices are multiples of 1000
                $uniqueCode = $nominal % 1000;

                // Skip if there is no unique code (reads as 000) or nominal is too small
                if ($nominal > 0 && $uniqueCode > 0) {

                    // 1. Check Standard Invoices (filter by current billing period to avoid false match with old static unique codes)
                    $invoice = Invoice::where('status', 'unpaid')
                        ->where('unique_code', $uniqueCode)
                        ->where('total_amount', $nominal)
                        ->where('billing_period', now()->format('Y-m'))
                        ->first();

                    if ($invoice) {
                        $invoice->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                            'payment_method' => $paymentMethod,
                        ]);

                        Log::info("Auto-Verified Invoice ID: {$invoice->invoice_number} detected payment: Rp {$nominal} with unique code: {$uniqueCode}");

                        RemoveIsolirJob::dispatch($invoice->customer);
                        SendManualInvoiceWhatsappJob::dispatch($invoice);

                        $verified = true;
                        $processedItem = $invoice;
                        break;
                    }

                    // 2. Check Voucher Orders
                    // Note: VoucherOrder matches total_price + unique_amount directly
                    // We don't filter by uniqueCode because discounts can make total_price non-multiple of 1000
                    $voucherOrder = VoucherOrder::where('payment_status', 'unpaid')
                        ->whereRaw('(total_price + unique_amount) = ?', [$nominal])
                        ->first();

                    if ($voucherOrder) {
                        $voucherOrder->update([
                            'payment_status' => 'paid',
                            'paid_at' => now(),
                        ]);

                        Log::info("Auto-Verified Voucher Order: {$voucherOrder->order_code} detected payment: Rp {$nominal} with unique code: {$uniqueCode}");

                        // Dispatch Job for Generation & MikroTik Sync
                        BulkGenerateHotspotVouchersJob::dispatch(
                            $voucherOrder->user_id,
                            $voucherOrder->package_id,
                            $voucherOrder->quantity,
                            $voucherOrder->id
                        );

                        $verified = true;
                        $processedItem = $voucherOrder;
                        break;
                    }
                }
            }
        }

        if ($verified) {
            return response()->json([
                'message' => 'Payment verified and item updated automatically',
                'type' => isset($processedItem->invoice_number) ? 'invoice' : 'voucher_order',
                'data' => $processedItem
            ], 200);
        }

        return response()->json([
            'message' => 'Payment notification received, but no matching unpaid item found.',
            'data' => $payload
        ], 200);
    }
}
