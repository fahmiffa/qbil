<?php

use Illuminate\Support\Facades\DB;

$period = now()->format('Y-m');

echo "=== KAPAN INVOICE {$period} DIBUAT (distribusi created_at) ===\n";
foreach (DB::table('invoices')->where('billing_period', $period)
    ->selectRaw("DATE(created_at) d, COUNT(*) total, SUM(status='paid') paid, SUM(status='unpaid') unpaid")
    ->groupBy(DB::raw('DATE(created_at)'))->orderBy('d')->get() as $r) {
    echo "   {$r->d} | total={$r->total} | paid={$r->paid} unpaid={$r->unpaid}\n";
}

echo "\n=== INVOICE {$period}: due_date terkecil/terbesar ===\n";
$r = DB::table('invoices')->where('billing_period', $period)
    ->selectRaw('MIN(due_date) mind, MAX(due_date) maxd')->first();
echo "   min due={$r->mind} max due={$r->maxd}\n";

echo "\n=== TABEL JOBS (queue) terakhir 20 ===\n";
foreach (DB::table('jobs')->orderByDesc('id')->limit(20)->get() as $j) {
    $payload = json_decode($j->payload, true);
    $cmd = $payload['displayName'] ?? ($payload['data']['commandName'] ?? '?');
    echo "   #{$j->id} {$cmd} available_at={$j->available_at} attempts={$j->attempts}\n";
}
$cnt = DB::table('jobs')->count();
echo "   total jobs di queue sekarang: {$cnt}\n";

echo "\n=== FAILED JOBS terakhir 10 ===\n";
if (DB::table('failed_jobs')->exists()) {
    foreach (DB::table('failed_jobs')->orderByDesc('id')->limit(10)->get() as $j) {
        $payload = json_decode($j->payload, true);
        $cmd = $payload['displayName'] ?? '?';
        echo "   #{$j->id} {$cmd} failed_at={$j->failed_at}\n";
    }
} else {
    echo "   (tidak ada / tabel tidak ada)\n";
}

echo "\n=== APP SETTINGS user 5 ===\n";
$s = DB::table('app_settings')->where('user_id', 5)->first();
print_r($s);

echo "\n=== USER 5 features ===\n";
$u = DB::table('users')->where('id', 5)->first();
echo "   name={$u->name} email={$u->email}\n";
foreach (DB::table('features')->where('user_id', 5)->get() as $f) {
    echo "   feature: {$f->key} = " . var_export($f->enabled, true) . "\n";
}
