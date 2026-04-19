<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
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
}
