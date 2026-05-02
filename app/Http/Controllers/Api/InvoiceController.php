<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Customer;
use App\Services\QrisLogic;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices with customer names.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Fetch invoices belonging to the authenticated user's customers
        $user = auth('api')->user();
        
        $invoices = Invoice::whereHas('customer', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['customer:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($invoices);
    }

    /**
     * Display the specified invoice.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = auth('api')->user();

        $invoice = Invoice::where('id', $id)
            ->whereHas('customer', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['customer:id,name'])
            ->first();

        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        return response()->json($invoice);
    }

    /**
     * Confirm/Mark invoice as paid.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmPayment($id)
    {
        $user = auth('api')->user();

        $invoice = Invoice::where('id', $id)
            ->whereHas('customer', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->first();

        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        if ($invoice->status === 'paid') {
            return response()->json(['message' => 'Invoice is already paid'], 400);
        }

        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return response()->json([
            'message' => 'Payment confirmed successfully',
            'invoice' => $invoice
        ]);
    }

    /**
     * Lookup unpaid invoices for a customer by username or IP address.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function lookup(Request $request)
    {
        $identifier = $request->get('identifier');
        
        if (!$identifier) {
            return response()->json(['message' => 'Identifier is required (username for PPPoE or IP for Static)'], 400);
        }

        // Cari pelanggan berdasarkan username (jika PPPoE) atau IP (jika Static)
        $customer = Customer::where(function($q) use ($identifier) {
                $q->where('service_type', 'pppoe')->where('username', $identifier)
                  ->orWhere('service_type', 'static')->where('ip_address', $identifier);
            })
            ->with(['user.appSetting'])
            ->first();

        if (!$customer) {
            return response()->json(['message' => 'Customer not found with the provided identifier'], 404);
        }

        // Ambil invoice yang belum lunas
        $invoices = $customer->invoices()
            ->where('status', 'unpaid')
            ->orderBy('created_at', 'desc')
            ->get();

        // Generate Dynamic QRIS for each invoice if static QR is available
        $appSetting = $customer->user->appSetting;
        if ($appSetting && $appSetting->qr) {
            $invoices->map(function ($invoice) use ($appSetting) {
                try {
                    $invoice->qris_payload = QrisLogic::generateDynamicQris(
                        $appSetting->qr, 
                        $invoice->total_amount
                    );
                } catch (\Exception $e) {
                    $invoice->qris_payload = null;
                }
                return $invoice;
            });
        }

        return response()->json([
            'status' => 'success',
            'admin' => [
                'name' => $customer->user->name,
                'phone' => $customer->user->phone,
                'photo' => $customer->user->photo ? url('storage/' . $customer->user->photo) : null,
                'payment_instruction' => $customer->user->appSetting->payment_instruction ?? null,
                'qr_code' => $customer->user->appSetting->qr ?? null,
            ],
            'customer' => [
                'id_pelanggan' => $customer->id_pelanggan,
                'name' => $customer->name,
                'service_type' => $customer->service_type,
                'status' => $customer->status,
            ],
            'invoices' => $invoices
        ]);
    }
}
