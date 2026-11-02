<?php

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;

$period = now()->format('Y-m');
echo "=== PERIODE DASHBOARD SEKARANG: {$period} ===\n\n";

// 1. Total customer per user (angka di halaman Pelanggan)
echo "1. JUMLAH CUSTOMER (halaman Pelanggan)\n";
$tot = 0;
foreach (DB::table('customers')->selectRaw('user_id, COUNT(*) total')->groupBy('user_id')->orderBy('user_id')->get() as $r) {
    $u = User::find($r->user_id);
    echo "   user_id={$r->user_id} (" . ($u->name ?? '?') . ") = {$r->total}\n";
    $tot += $r->total;
}
echo "   TOTAL = {$tot}\n\n";

// 2. Hasil query dashboard persis (invoice periode ini, customer milik user)
echo "2. HASIL QUERY DASHBOARD (invoice periode ini)\n";
foreach (DB::table('invoices')
    ->join('customers', 'invoices.customer_id', '=', 'customers.id')
    ->where('invoices.billing_period', $period)
    ->selectRaw("customers.user_id, COUNT(*) total_invoice,
        SUM(CASE WHEN invoices.status='paid' THEN 1 ELSE 0 END) paid,
        SUM(CASE WHEN invoices.status='unpaid' THEN 1 ELSE 0 END) unpaid,
        SUM(CASE WHEN invoices.status='canceled' THEN 1 ELSE 0 END) canceled,
        SUM(CASE WHEN invoices.status NOT IN ('paid','unpaid','canceled') THEN 1 ELSE 0 END) status_lain")
    ->groupBy('customers.user_id')->orderBy('customers.user_id')->get() as $r) {
    $u = User::find($r->user_id);
    echo "   user_id={$r->user_id} (" . ($u->name ?? '?') . ") invoice={$r->total_invoice} | paid={$r->paid} unpaid={$r->unpaid} canceled={$r->canceled} lain={$r->status_lain}\n";
}
echo "\n";

// 3. Distribusi status invoice periode ini
echo "3. DISTRIBUSI STATUS INVOICE periode {$period} (semua, tanpa join)\n";
foreach (DB::table('invoices')->where('billing_period', $period)
    ->selectRaw('status, COUNT(*) total')->groupBy('status')->orderBy('status')->get() as $r) {
    echo "   status={$r->status} = {$r->total}\n";
}
echo "\n";

// 4. Status invoice di SEMUA periode (untuk lihat invoice lama yang tidak masuk dashboard)
echo "4. DISTRIBUSI STATUS INVOICE SEMUA PERIODE\n";
foreach (DB::table('invoices')->selectRaw('billing_period, status, COUNT(*) total')
    ->groupBy('billing_period', 'status')->orderBy('billing_period')->get() as $r) {
    echo "   {$r->billing_period} | {$r->status} = {$r->total}\n";
}
echo "\n";

// 5. Customer yang TIDAK punya invoice di periode ini, dirinci per penyebab
echo "5. CUSTOMER TANPA INVOICE periode {$period} (per user, dirinci)\n";
foreach (DB::table('customers')
    ->leftJoin('invoices', function ($j) use ($period) {
        $j->on('customers.id', '=', 'invoices.customer_id')
          ->where('invoices.billing_period', '=', $period);
    })
    ->selectRaw("customers.user_id,
        COUNT(*) total_customer,
        SUM(invoices.id IS NULL) tanpa_invoice,
        SUM(invoices.id IS NULL AND customers.status != 'active') tanpa_karena_suspended,
        SUM(invoices.id IS NULL AND customers.status = 'active' AND customers.package_id IS NULL) tanpa_karena_package_null,
        SUM(invoices.id IS NULL AND customers.status = 'active' AND customers.due_date IS NULL) tanpa_karena_due_date_null,
        SUM(invoices.id IS NULL AND customers.status = 'active' AND (customers.phone IS NULL OR customers.phone = '')) tanpa_karena_phone_kosong,
        SUM(invoices.id IS NULL AND customers.status = 'active' AND customers.package_id IS NOT NULL AND customers.due_date IS NOT NULL AND customers.phone IS NOT NULL AND customers.phone != '') tanpa_padahal_lengkap")
    ->groupBy('customers.user_id')->orderBy('customers.user_id')->get() as $r) {
    $u = User::find($r->user_id);
    echo "   user_id={$r->user_id} (" . ($u->name ?? '?') . ")\n";
    echo "     total customer          : {$r->total_customer}\n";
    echo "     tanpa invoice periode ini: {$r->tanpa_invoice}\n";
    echo "       - suspended            : {$r->tanpa_karena_suspended}\n";
    echo "       - package null         : {$r->tanpa_karena_package_null}\n";
    echo "       - due_date null        : {$r->tanpa_karena_due_date_null}\n";
    echo "       - phone kosong         : {$r->tanpa_karena_phone_kosong}\n";
    echo "       - data lengkap + aktif : {$r->tanpa_padahal_lengkap}\n";
}
echo "\n";

// 6. Invoice yatim (customer_id tidak ada di tabel customers)
echo "6. INVOICE TANPA CUSTOMER VALID (akan hilang dari dashboard)\n";
$orphan = DB::table('invoices')->leftJoin('customers', 'invoices.customer_id', '=', 'customers.id')
    ->whereNull('customers.id')->count();
echo "   total orphan = {$orphan}\n\n";

// 7. Customer dengan LEBIH dari 1 invoice di periode ini
echo "7. CUSTOMER DENGAN LEBIH DARI 1 INVOICE periode {$period}\n";
foreach (DB::table('invoices')
    ->join('customers', 'invoices.customer_id', '=', 'customers.id')
    ->where('invoices.billing_period', $period)
    ->selectRaw('customers.user_id, COUNT(DISTINCT invoices.customer_id) jml_customer_multi, COUNT(*) jml_invoice')
    ->groupBy('customers.user_id')
    ->havingRaw('COUNT(*) > COUNT(DISTINCT invoices.customer_id)')
    ->get() as $r) {
    echo "   user_id={$r->user_id}: {$r->jml_invoice} invoice utk {$r->jml_customer_multi} customer (ada duplikat)\n";
}
if (!DB::table('invoices')->where('billing_period', $period)->exists()) {
    echo "   (tidak ada data)\n";
}
echo "\n";

// 8. Konfigurasi app_settings tiap user (waktu & offset generate invoice)
echo "8. APP SETTINGS per user (invoice_gen_days / invoice_gen_time)\n";
foreach (DB::table('app_settings')->get() as $s) {
    echo "   user_id={$s->user_id} invoice_gen_days={$s->invoice_gen_days} invoice_gen_time={$s->invoice_gen_time}\n";
}
