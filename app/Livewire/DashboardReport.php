<?php

namespace App\Livewire;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Package;
use App\Models\HotspotUser;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardReport extends Component
{
    public $month;
    public $year;
    public $showNominal = true;

    public function mount()
    {
        $this->month = now()->format('m');
        $this->year = now()->format('Y');

        // Load preference from session or default
        $this->showNominal = session('dashboard_show_nominal', true);
    }

    public function toggleNominal()
    {
        $this->showNominal = !$this->showNominal;
        session(['dashboard_show_nominal' => $this->showNominal]);
    }

    public function render()
    {
        $period = $this->year . '-' . $this->month;
        $userId = auth()->id();

        // Total Statistics from Transaction (actual cash flow)
        $totalPaidIncome = \App\Models\Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereYear('transaction_date', $this->year)
            ->whereMonth('transaction_date', $this->month)
            ->sum('amount');

        $countPaidTransactions = \App\Models\Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereYear('transaction_date', $this->year)
            ->whereMonth('transaction_date', $this->month)
            ->count();

        // Piutang Tagihan yang Belum Terbayar (Tetap Berbasis Periode Tagihan Aktif)
        $unpaidStats = Invoice::whereHas('customer', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->where('billing_period', $period)
            ->where('status', 'unpaid')
            ->select(
                DB::raw('SUM(total_amount) as total_unpaid'),
                DB::raw('COUNT(*) as count_unpaid')
            )
            ->first();

        // Rincian Nominal Terbayar untuk Invoice Bulanan (Berdasarkan Tanggal Pembayaran di Bulan Ini)
        $invoicePaidStats = Invoice::whereHas('customer', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->where('status', 'paid')
            ->whereYear('paid_at', $this->year)
            ->whereMonth('paid_at', $this->month)
            ->select(
                DB::raw('SUM(amount) as total_paid_base'),
                DB::raw('SUM(unique_code) as total_paid_unique')
            )
            ->first();

        // Konstruksi objek stats untuk digunakan di blade template (menjaga kompatibilitas)
        $stats = (object) [
            'total_paid' => $totalPaidIncome,
            'count_paid' => $countPaidTransactions,
            'total_paid_base' => $invoicePaidStats->total_paid_base ?? 0,
            'total_paid_unique' => $invoicePaidStats->total_paid_unique ?? 0,
            'total_unpaid' => $unpaidStats->total_unpaid ?? 0,
            'count_unpaid' => $unpaidStats->count_unpaid ?? 0,
        ];

        // Breakdown by Service Type (PPPOE, STATIC, HOTSPOT)
        $allowedTypes = [];
        if (auth()->user()->hasFeature('pppoe')) $allowedTypes[] = 'PPPOE';
        if (auth()->user()->hasFeature('static')) $allowedTypes[] = 'STATIC';
        if (auth()->user()->hasFeature('hotspot')) $allowedTypes[] = 'HOTSPOT';

        // Fallback for UI if no specific features yet but has general mikrotik (legacy support during migration)
        if (empty($allowedTypes) && auth()->user()->hasFeature('mikrotik')) {
            $allowedTypes = ['PPPOE', 'STATIC', 'HOTSPOT'];
        } elseif (empty($allowedTypes)) {
            $allowedTypes = ['STATIC'];
        }

        $serviceBreakdown = collect($allowedTypes)->map(function ($tipe) use ($userId, $period) {
            $activeCount = 0;
            $suspendCount = 0;
            $potential = 0;

            if (in_array(strtoupper($tipe), ['PPPOE', 'STATIC'])) {
                $activeCount = Customer::where('user_id', $userId)
                    ->where('service_type', strtolower($tipe))
                    ->where('status', 'active')
                    ->count();

                $suspendCount = Customer::where('user_id', $userId)
                    ->where('service_type', strtolower($tipe))
                    ->where('status', 'suspended')
                    ->count();

                $potential = Customer::where('customers.user_id', $userId)
                    ->where('customers.service_type', strtolower($tipe))
                    ->where('customers.status', 'active')
                    ->join('packages', 'customers.package_id', '=', 'packages.id')
                    ->sum('packages.price');
            } else {
                // HOTSPOT
                $activeCount = HotspotUser::where('user_id', $userId)->count();
                $suspendCount = 0; // Hotspot users don't have suspended status in this model
                $potential = HotspotUser::where('hotspot_users.user_id', $userId)
                    ->join('packages', 'hotspot_users.package_id', '=', 'packages.id')
                    ->sum('packages.price');
            }

            // Actual Invoice Stats for this period
            $invoiceStats = Invoice::whereHas('customer', function ($q) use ($userId, $tipe) {
                $q->where('user_id', $userId)
                    ->whereHas('package', function ($pq) use ($tipe) {
                        $pq->where('tipe', $tipe);
                    });
            })
                ->where('billing_period', $period)
                ->select(
                    DB::raw('SUM(total_amount) as total'),
                    DB::raw('SUM(CASE WHEN invoices.status = "paid" THEN total_amount ELSE 0 END) as paid')
                )
                ->first();

            // Also check invoices directly linked via package_id (for hotspot users if any)
            $hotspotInvoiceStats = Invoice::where('package_id', '!=', null)
                ->whereHas('package', function ($q) use ($tipe, $userId) {
                    $q->where('tipe', $tipe)->where('user_id', $userId);
                })
                ->where('billing_period', $period)
                ->select(
                    DB::raw('SUM(total_amount) as total'),
                    DB::raw('SUM(CASE WHEN invoices.status = "paid" THEN total_amount ELSE 0 END) as paid')
                )
                ->first();

            $finalTotal = max($invoiceStats->total ?? 0, $hotspotInvoiceStats->total ?? 0);
            $finalPaid = max($invoiceStats->paid ?? 0, $hotspotInvoiceStats->paid ?? 0);

            return (object) [
                'tipe' => strtoupper($tipe),
                'total' => $finalTotal,
                'paid' => $finalPaid,
                'active_count' => $activeCount,
                'suspend_count' => $suspendCount,
                'potential' => $potential
            ];
        });

        // Customer Locations for Map
        $mapData = Customer::where('user_id', $userId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select('name', 'latitude', 'longitude', 'status', 'service_type', 'asset_id')
            ->get();

        // Asset Locations for Map
        $assetsData = \App\Models\Asset::where('user_id', $userId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select('id', 'name', 'latitude', 'longitude', 'category')
            ->get();

        return view('livewire.dashboard-report', [
            'stats' => $stats,
            'serviceBreakdown' => $serviceBreakdown,
            'mapData' => $mapData,
            'assetsData' => $assetsData
        ])->layout('layouts.app', ['header' => 'Dashboard']);
    }
}
