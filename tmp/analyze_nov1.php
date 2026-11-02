<?php

use Illuminate\Support\Facades\DB;

echo "=== INVOICE 2026-11 yang dibuat 2026-11-01: distribusi due day ===\n";
foreach (DB::table('invoices')
    ->where('billing_period', '2026-11')
    ->whereBetween('created_at', ['2026-11-01 00:00:00', '2026-11-01 23:59:59'])
    ->selectRaw('DAY(due_date) day, COUNT(*) total, MIN(created_at) mint, MAX(created_at) maxt')
    ->groupBy(DB::raw('DAY(due_date)'))->orderBy('day')->get() as $r) {
    echo "   due day={$r->day} | total={$r->total} | {$r->mint} s/d {$r->maxt}\n";
}

echo "\n=== INVOICE 2026-11 yang dibuat 27-31 Okt: distribusi due day ===\n";
foreach (DB::table('invoices')
    ->where('billing_period', '2026-11')
    ->whereBetween('created_at', ['2026-10-27 00:00:00', '2026-10-31 23:59:59'])
    ->selectRaw('DAY(due_date) day, COUNT(*) total')
    ->groupBy(DB::raw('DAY(due_date)'))->orderBy('day')->get() as $r) {
    echo "   due day={$r->day} | total={$r->total}\n";
}

echo "\n=== FAILED JOB GenerateMonthlyInvoicesJob (exception) ===\n";
$fj = DB::table('failed_jobs')->where('id', 1)->first();
if ($fj) {
    echo "   failed_at: {$fj->failed_at}\n";
    echo "   exception: " . substr($fj->exception, 0, 1200) . "\n";
    $p = json_decode($fj->payload, true);
    echo "   command: " . ($p['data']['commandName'] ?? '?') . "\n";
    echo "   uuid: " . ($p['uuid'] ?? '?') . "\n";
}

echo "\n=== Apakah ada command invoice:generate-emergency / invoice:generate di log artisan? ===\n";
