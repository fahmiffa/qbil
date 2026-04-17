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

    public function mount()
    {
        $this->month = now()->format('m');
        $this->year = now()->format('Y');
    }

    public function render()
    {
        $period = $this->year . '-' . $this->month;
        $userId = auth()->id();

        // Total Statistics
        $stats = Invoice::whereHas('customer', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->where('billing_period', $period)
            ->select(
                DB::raw('SUM(CASE WHEN invoices.status = "paid" THEN total_amount ELSE 0 END) as total_paid'),
                DB::raw('SUM(CASE WHEN invoices.status = "unpaid" THEN total_amount ELSE 0 END) as total_unpaid'),
                DB::raw('COUNT(CASE WHEN invoices.status = "paid" THEN 1 END) as count_paid'),
                DB::raw('COUNT(CASE WHEN invoices.status = "unpaid" THEN 1 END) as count_unpaid')
            )
            ->first();

        // Breakdown by Service Type (PPPOE vs HOTSPOT)
        $serviceBreakdown = collect(['PPPOE', 'hotspot'])->map(function ($tipe) use ($userId, $period) {
            // Count Active Users
            if (strtoupper($tipe) === 'PPPOE') {
                $count = Customer::where('user_id', $userId)->where('status', 'active')->count();
                $potential = Customer::where('customers.user_id', $userId)->where('customers.status', 'active')
                    ->join('packages', 'customers.package_id', '=', 'packages.id')
                    ->sum('packages.price');
            } else {
                $count = HotspotUser::where('user_id', $userId)->count();
                $potential = HotspotUser::where('hotspot_users.user_id', $userId)
                    ->join('packages', 'hotspot_users.package_id', '=', 'packages.id')
                    ->sum('packages.price');
            }

            // Actual Invoice Stats for this period
            $invoiceStats = Invoice::whereHas('customer', function ($q) use ($userId, $tipe) {
                    $q->where('user_id', $userId)
                      ->whereHas('package', function($pq) use ($tipe) {
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
                ->whereHas('package', function($q) use ($tipe, $userId) {
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
                'count' => $count,
                'potential' => $potential
            ];
        });

        // Detailed List for Table
        $invoices = Invoice::whereHas('customer', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->with(['customer.package'])
            ->where('billing_period', $period)
            ->orderBy('status', 'asc') // unpaid first
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.dashboard-report', [
            'stats' => $stats,
            'serviceBreakdown' => $serviceBreakdown,
            'invoices' => $invoices
        ])->layout('layouts.app', ['header' => 'Ringkasan Laporan']);
    }
}
