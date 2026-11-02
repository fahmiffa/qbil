<?php

use Illuminate\Support\Facades\DB;

$period = now()->format('Y-m');
$userIds = [5];

echo "=== RIWAYAT INVOICE 85 CUSTOMER AKTIF TANPA INVOICE {$period} ===\n\n";

$missingIds = DB::table('customers')
    ->leftJoin('invoices', function ($j) use ($period) {
        $j->on('customers.id', '=', 'invoices.customer_id')
          ->where('invoices.billing_period', '=', $period);
    })
    ->where('customers.user_id', 5)
    ->where('customers.status', 'active')
    ->whereNotNull('customers.package_id')
    ->whereNotNull('customers.due_date')
    ->whereNotNull('customers.phone')
    ->where('customers.phone', '!=', '')
    ->whereNull('invoices.id')
    ->pluck('customers.id');

echo "Jumlah: " . $missingIds->count() . "\n\n";

// Berapa banyak yang PUNYA invoice periode sebelumnya (2026-10)?
echo "A. Invoice 2026-10 untuk 85 customer tsb:\n";
$oct = DB::table('invoices')->whereIn('customer_id', $missingIds)
    ->where('billing_period', '2026-10')
    ->selectRaw('status, COUNT(*) total')->groupBy('status')->get();
foreach ($oct as $r) echo "   {$r->status} = {$r->total}\n";
if ($oct->isEmpty()) echo "   (tidak ada)\n";

echo "\nB. Invoice 2026-09 untuk 85 customer tsb:\n";
$sep = DB::table('invoices')->whereIn('customer_id', $missingIds)
    ->where('billing_period', '2026-09')
    ->selectRaw('status, COUNT(*) total')->groupBy('status')->get();
foreach ($sep as $r) echo "   {$r->status} = {$r->total}\n";
if ($sep->isEmpty()) echo "   (tidak ada)\n";

echo "\nC. Periode invoice TERAKHIR milik 85 customer tsb (status terakhir):\n";
foreach (DB::table('invoices')->whereIn('customer_id', $missingIds)
    ->selectRaw('customer_id, MAX(billing_period) last_period')
    ->groupBy('customer_id')->get() as $r) {
    $inv = DB::table('invoices')->where('customer_id', $r->customer_id)
        ->where('billing_period', $r->last_period)->first();
    $due = DB::table('customers')->where('id', $r->customer_id)->value('due_date');
    echo "   cust#{$r->customer_id} | due_now={$due} | last_invoice={$r->last_period} ({$inv->status}) | paid_at={$inv->paid_at}\n";
}

echo "\nD. Status aktivasi: berapa yang activated_at November vs sebelumnya:\n";
foreach (DB::table('customers')->whereIn('id', $missingIds)
    ->selectRaw("DATE_FORMAT(activated_at, '%Y-%m') am, COUNT(*) total")
    ->groupBy(DB::raw("DATE_FORMAT(activated_at, '%Y-%m')"))->get() as $r) {
    echo "   {$r->am} = {$r->total}\n";
}

echo "\nE. Berapa dari 85 yang PUNYA invoice 2026-11 dengan status canceled/expired? (harusnya 0)\n";
$c = DB::table('invoices')->whereIn('customer_id', $missingIds)
    ->where('billing_period', $period)->count();
echo "   total invoice 2026-11 milik mereka = {$c}\n";
