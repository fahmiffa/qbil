<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        // Sample Payload:
        // {"id":273397,"title":"Financial Diary","text":"You spent IDR 85,000.00 at Groceries.","package":"com.bca.mybca.omni.android","postTime":1775903218006}
        
        $payload = $request->validate([
            'id' => 'required',
            'title' => 'nullable|string',
            'text' => 'required|string',
            'package' => 'required|string',
            'postTime' => 'required'
        ]);

        // Extract number from text. Also include title just in case the amount is there
        $textToParse = $payload['title'] . ' ' . $payload['text'];
        
        // Find all sequences that look like numbers/currency: e.g. 85,000.00 or 150.000 or 150000
        preg_match_all('/(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{1,2})?)/', $textToParse, $matches);
        
        $verified = false;
        $processedInvoice = null;

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
                    // Match as primary against unique_code and total_amount
                    $invoice = \App\Models\Invoice::where('status', 'unpaid')
                        ->where('unique_code', $uniqueCode)
                        ->where('total_amount', $nominal)
                        ->first();

                    if ($invoice) {
                        $invoice->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                        ]);

                        Log::info("Auto-Verified Invoice ID: {$invoice->invoice_number} detected payment: Rp {$nominal} with unique code: {$uniqueCode}");
                        
                        // Dispatch job untuk menghapus IP pelanggan dari address-list ISLOR di Mikrotik
                        \App\Jobs\RemoveIsolirJob::dispatch($invoice->customer);

                        $verified = true;
                        $processedInvoice = $invoice;
                        break; // Stop after successfully matching & updating the invoice
                    }
                }
            }
        }

        if ($verified) {
            return response()->json([
                'message' => 'Payment verified and invoice updated automatically',
                'data' => $processedInvoice
            ], 200);
        }

        return response()->json([
            'message' => 'Payment notification received, but no matching unpaid invoice found.',
            'data' => $payload
        ], 200);
    }
}
