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
                DB::raw('SUM(CASE WHEN invoices.status = "paid" THEN amount ELSE 0 END) as total_paid_base'),
                DB::raw('SUM(CASE WHEN invoices.status = "paid" THEN unique_code ELSE 0 END) as total_paid_unique'),
                DB::raw('SUM(CASE WHEN invoices.status = "unpaid" THEN total_amount ELSE 0 END) as total_unpaid'),
                DB::raw('COUNT(CASE WHEN invoices.status = "paid" THEN 1 END) as count_paid'),
                DB::raw('COUNT(CASE WHEN invoices.status = "unpaid" THEN 1 END) as count_unpaid')
            )
            ->first();

        // Breakdown by Service Type (PPPOE, STATIC, HOTSPOT)
        $serviceBreakdown = collect(['PPPOE', 'STATIC', 'HOTSPOT'])->map(function ($tipe) use ($userId, $period) {
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
                'active_count' => $activeCount,
                'suspend_count' => $suspendCount,
                'potential' => $potential
            ];
        });

        // Customer Locations for Map
        $mapData = Customer::where('user_id', $userId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select('name', 'latitude', 'longitude', 'status', 'service_type')
            ->get();

        return view('livewire.dashboard-report', [
            'stats' => $stats,
            'serviceBreakdown' => $serviceBreakdown,
            'mapData' => $mapData
        ])->layout('layouts.app', ['header' => 'Ringkasan Laporan']);
    }
}
