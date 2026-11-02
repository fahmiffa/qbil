<?php

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

$period = now()->format('Y-m');

echo "=== DETAIL 85 CUSTOMER AKTIF + DATA LENGKAP TANPA INVOICE {$period} ===\n\n";

$missing = DB::table('customers')
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
    ->select('customers.*')
    ->get();

echo "Jumlah: " . $missing->count() . "\n\n";

// Distribusi hari jatuh tempo
echo "A. Distribusi HARI jatuh tempo (due day):\n";
foreach ($missing->groupBy(fn($c) => substr($c->due_date, 8, 2))->map->count() as $day => $n) {
    echo "   tgl {$day} = {$n} customer\n";
}

echo "\nB. Distribusi service_type:\n";
foreach ($missing->groupBy('service_type')->map->count() as $t => $n) {
    echo "   {$t} = {$n}\n";
}

echo "\nC. Distribusi creation_method:\n";
foreach ($missing->groupBy('creation_method')->map->count() as $m => $n) {
    echo "   {$m} = {$n}\n";
}

echo "\nD. Distribusi activated_at (bulan):\n";
foreach ($missing->groupBy(fn($c) => $c->activated_at ? substr($c->activated_at, 0, 7) : 'NULL')->map->count() as $m => $n) {
    echo "   {$m} = {$n}\n";
}

echo "\nE. Distribusi created_at (bulan):\n";
foreach ($missing->groupBy(fn($c) => substr($c->created_at, 0, 7))->map->count() as $m => $n) {
    echo "   {$m} = {$n}\n";
}

echo "\nF. Contoh 20 customer yang hilang (id, name, due_date, service_type, creation_method, created_at):\n";
foreach ($missing->take(20) as $c) {
    echo "   #{$c->id} {$c->name} | due={$c->due_date} | {$c->service_type} | {$c->creation_method} | created={$c->created_at}\n";
}

// Bandingkan: distribusi due day customer YANG PUNYA invoice periode ini
echo "\nG. Pembanding: distribusi due day customer YANG PUNYA invoice {$period}:\n";
$has = DB::table('customers')
    ->join('invoices', 'customers.id', '=', 'invoices.customer_id')
    ->where('customers.user_id', 5)
    ->where('invoices.billing_period', $period)
    ->select('customers.due_date')
    ->get();
foreach ($has->groupBy(fn($c) => substr($c->due_date, 8, 2))->map->count() as $day => $n) {
    echo "   tgl {$day} = {$n} customer\n";
}

// H. Customer dengan due day yang TIDAK ADA di bulan November (29/30/31)
echo "\nH. Customer aktif user 5 dengan due day 29/30/31 (bulan Nov tidak ada tgl tsb utk 31):\n";
foreach (DB::table('customers')
    ->where('user_id', 5)->where('status', 'active')
    ->whereNotNull('due_date')
    ->whereIn(DB::raw('DAY(due_date)'), [29, 30, 31])
    ->selectRaw('DAY(due_date) day, COUNT(*) total')
    ->groupBy(DB::raw('DAY(due_date)'))
    ->get() as $r) {
    echo "   day={$r->day} total={$r->total}\n";
}
