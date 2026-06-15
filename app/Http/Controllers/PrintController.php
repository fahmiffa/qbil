<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Deposit;
use App\Models\Piutang;
use App\Models\HotspotUser;

use Illuminate\Http\Request;

class PrintController extends Controller
{
    public function invoice(Invoice $invoice)
    {
        app()->setLocale('id');
        $invoice->load(['customer.user.appSetting', 'package']);
        return view('print.unified', [
            'type' => 'Invoice',
            'data' => $invoice,
            'customer' => $invoice->customer,
            'number' => $invoice->invoice_number,
            'status' => $invoice->status,
            'amount' => $invoice->total_amount,
            'date' => $invoice->created_at,
            'items' => [
                ['label' => $invoice->package->name ?? 'Layanan Internet', 'value' => $invoice->amount],
                ['label' => 'Kode Unik Verifikasi', 'value' => $invoice->unique_code, 'is_code' => true]
            ]
        ]);
    }

    public function bulkInvoices(Request $request)
    {
        app()->setLocale('id');
        $ids = explode(',', $request->query('ids', ''));
        $invoices = Invoice::whereIn('id', $ids)->with(['customer.user.appSetting', 'package'])->orderBy('billing_period', 'asc')->get();

        if ($invoices->isEmpty()) abort(404);

        $customer = $invoices->first()->customer;
        $totalAmount = $invoices->sum('total_amount');

        return view('print.bulk-invoice-view', [
            'invoices' => $invoices,
            'customer' => $customer,
            'totalAmount' => $totalAmount,
            'invoice_number' => 'MB-' . strtoupper(substr(md5(time()), 0, 8)),
        ]);
    }

    public function deposit(Deposit $deposit)
    {
        $deposit->load(['customer.user.appSetting', 'package']);

        // Generate month list from relation if available, otherwise from dates
        \App::setLocale('id');
        $monthsData = [];
        if ($deposit->months->count() > 0) {
            foreach ($deposit->months->sortBy('month') as $dm) {
                $monthObj = is_string($dm->month) ? \Carbon\Carbon::parse($dm->month) : $dm->month;
                $monthsData[] = [
                    'label' => 'Bulan: ' . $monthObj->translatedFormat('F Y'),
                    'value' => $deposit->amount_per_month
                ];
            }
        } elseif ($deposit->start_date && $deposit->end_date) {
            $current = $deposit->start_date->copy()->startOfMonth();
            $end = $deposit->end_date->copy()->startOfMonth();
            while ($current <= $end) {
                $monthsData[] = [
                    'label' => 'Bulan: ' . $current->translatedFormat('F Y'),
                    'value' => $deposit->amount_per_month
                ];
                $current->addMonth();
            }
        }

        $items = [
            ['label' => 'Pembayaran Deposit (' . count($monthsData) . ' Bulan)', 'value' => null],
            ['label' => 'Paket: ' . ($deposit->package->name ?? 'N/A'), 'value' => null, 'is_sub' => true],
        ];

        foreach ($monthsData as $m) {
            $items[] = [
                'label' => $m['label'],
                'value' => $m['value'],
                'is_sub' => true
            ];
        }

        return view('print.unified', [
            'type' => 'Deposit',
            'data' => $deposit,
            'customer' => $deposit->customer,
            'number' => 'DEP-' . $deposit->created_at->format('Ymd') . '-' . substr($deposit->id, 0, 4),
            'status' => 'paid',
            'amount' => $deposit->total_amount,
            'date' => $deposit->payment_date,
            'items' => $items
        ]);
    }

    public function piutang(Piutang $piutang)
    {
        $piutang->load(['customer.user.appSetting']);
        $customer = $piutang->customer;

        // Ambil semua piutang yang belum lunas untuk pelanggan ini
        $unpaidPiutangs = Piutang::where('customer_id', $customer->id)
            ->where('status', 'unpaid')
            ->orderBy('created_at', 'asc')
            ->get();

        // Jika data yang diakses sudah lunas, tampilkan hanya data tersebut sebagai bukti tunggal
        if ($unpaidPiutangs->isEmpty() || $piutang->status == 'paid') {
            $unpaidPiutangs = collect([$piutang]);
        }

        $items = [
            ['label' => 'Rincian Piutang Terutang', 'value' => null],
        ];

        \Carbon\Carbon::setLocale('id');
        foreach ($unpaidPiutangs as $p) {
            $items[] = [
                'label' => 'Periode: ' . \Carbon\Carbon::parse($p->billing_period)->translatedFormat('F Y'),
                'value' => $p->amount,
                'is_sub' => true
            ];
        }

        return view('print.unified', [
            'type' => 'Piutang',
            'data' => $piutang,
            'customer' => $customer,
            'number' => 'PIU-' . now()->format('Ymd') . '-' . strtoupper(substr($customer->id, 0, 4)),
            'status' => $piutang->status, // Mengikuti status item utama yang diklik
            'amount' => $unpaidPiutangs->sum('amount'),
            'date' => now(),
            'items' => $items
        ]);
    }

    public function hotspotVouchers(Request $request)
    {
        $ids = explode(',', $request->ids);
        $vouchers = HotspotUser::with(['package', 'user'])
            ->whereIn('id', $ids)
            ->where('user_id', auth()->id())
            ->get();

        // Mark as printed to prevent re-printing
        HotspotUser::whereIn('id', $ids)->update(['is_printed' => true]);

        return view('print.hotspot-vouchers', [
            'vouchers' => $vouchers
        ]);
    }

    public function thermal(Invoice $invoice)
    {
        app()->setLocale('id');
        $invoice->load(['customer.user.appSetting', 'package']);
        return view('print.thermal', [
            'invoice' => $invoice
        ]);
    }

    public function reports(Request $request)
    {
        app()->setLocale('id');
        $userId = auth()->id();
        $startDate = $request->start_date ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d');
        $type = $request->type ?? 'income';
        $paymentMethod = $request->payment_method;
        $serviceType = $request->service_type;

        $baseQuery = \App\Models\Transaction::where('user_id', $userId)
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->where('type', $type);

        if ($paymentMethod) {
            if ($paymentMethod === 'none') {
                $baseQuery->where(function ($q) {
                    $q->whereNull('payment_method')->orWhere('payment_method', '');
                });
            } else {
                $baseQuery->where('payment_method', $paymentMethod);
            }
        }

        if ($serviceType) {
            $baseQuery->where('service_type', $serviceType);
        }

        $transactions = $baseQuery->orderBy('transaction_date', 'asc')->get();
        $totalAmount = $transactions->sum('amount');

        $user = \App\Models\User::with('appSetting')->find($userId);

        return view('print.reports', [
            'transactions' => $transactions,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'type' => $type,
            'totalAmount' => $totalAmount,
            'appSetting' => $user->appSetting ?? null,
            'user' => $user
        ]);
    }
}
