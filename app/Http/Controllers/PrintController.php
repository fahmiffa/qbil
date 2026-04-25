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

        foreach ($unpaidPiutangs as $p) {
            $items[] = [
                'label' => 'Periode: ' . $p->billing_period,
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
        $vouchers = HotspotUser::with('package')
            ->whereIn('id', $ids)
            ->where('user_id', auth()->id())
            ->get();
            
        return view('print.hotspot-vouchers', [
            'vouchers' => $vouchers
        ]);
    }
}

