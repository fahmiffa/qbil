<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\User;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateEmergencyInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:generate-emergency
                            {--user= : ID user spesifik (opsional, kosong = semua user)}
                            {--dry-run : Simulasi tanpa benar-benar membuat invoice}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate invoice DARURAT untuk pelanggan yang sudah melewati batas -offset hari sebelum jatuh tempo tapi belum punya invoice bulan depan';

    public function handle(): void
    {
        $dryRun    = $this->option('dry-run');
        $userId    = $this->option('user');
        $now       = now();
        $generated = 0;
        $skipped   = 0;

        $this->info('');
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║   GENERATE INVOICE DARURAT               ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->info('Waktu sekarang : ' . $now->format('d-m-Y H:i'));
        $this->info($dryRun ? '⚠  Mode: DRY-RUN (tidak ada yang disimpan)' : '🚀 Mode: EKSEKUSI NYATA');
        $this->info('');

        // Ambil user sesuai filter
        $query = User::with('appSetting');
        if ($userId) {
            $query->where('id', $userId);
        }
        $users = $query->get();

        foreach ($users as $user) {
            $setting = $user->appSetting;

            if (!$setting || (!$user->hasFeature('static') && !$user->hasFeature('pppoe'))) {
                $this->warn("  [Skip] User {$user->name}: Belum ada app-setting atau fitur layanan tidak aktif.");
                continue;
            }

            $offsetDays = (int) $setting->invoice_gen_days; // misal -5

            $this->info("👤 User: {$user->name} | Offset: {$offsetDays} hari");

            // Ambil semua pelanggan aktif dengan paket dan jatuh tempo
            $customers = Customer::where('user_id', $user->id)
                ->where('status', 'active')
                ->whereNotNull('package_id')
                ->whereNotNull('due_date')
                ->with('package')
                ->get();

            if ($customers->isEmpty()) {
                $this->line("  Tidak ada pelanggan aktif.");
                continue;
            }

            foreach ($customers as $customer) {
                $dueDay = Carbon::parse($customer->due_date)->format('d');

                // Hitung tanggal threshold: kapan invoice seharusnya sudah terbuat
                // Rumus: jatuh_tempo_bulan_ini - hari_ini >= offsetDays (negatif)
                // Artinya: invoice harusnya sudah terbuat jika hari ini >= (tanggal_jatuh_tempo + offset_hari)
                //
                // Contoh: jatuh tempo tgl 4, offset -5
                // Invoice harusnya dibuat pada 4 + (-5) = tgl 29 bulan sebelumnya
                // Jika hari ini >= 29 April, berarti sudah harus punya invoice untuk Mei

                // Cek di bulan ini dulu (jatuh tempo bulan ini)
                $dueDateThisMonth  = Carbon::createFromFormat('Y-m-d', $now->format('Y-m') . '-' . str_pad($dueDay, 2, '0', STR_PAD_LEFT));
                $thresholdThisMonth = $dueDateThisMonth->copy()->addDays($offsetDays);

                // Cek di bulan depan (jatuh tempo bulan depan)
                $nextMonth         = $now->copy()->addMonth();
                $dueDateNextMonth  = Carbon::createFromFormat('Y-m-d', $nextMonth->format('Y-m') . '-' . str_pad($dueDay, 2, '0', STR_PAD_LEFT));
                $thresholdNextMonth = $dueDateNextMonth->copy()->addDays($offsetDays);

                // Tentukan: invoice untuk periode mana yang perlu dicek?
                // Jika hari ini sudah >= threshold bulan depan → invoice untuk bulan depan
                // Jika hari ini sudah >= threshold bulan ini → invoice untuk bulan ini
                $targetPeriod = null;

                if ($now->gte($thresholdNextMonth->startOfDay()->copy())) {
                    $targetPeriod = $nextMonth->format('Y-m');
                } elseif ($now->gte($thresholdThisMonth->startOfDay()->copy())) {
                    $targetPeriod = $now->format('Y-m');
                }

                if (!$targetPeriod) {
                    $skipped++;
                    $this->line("  ⬜ [{$customer->name}] Belum waktunya (Threshold: {$thresholdThisMonth->format('d-m-Y')})");
                    continue;
                }

                // Cek apakah invoice periode tersebut sudah ada
                $exists = \App\Models\Invoice::where('customer_id', $customer->id)
                    ->where('billing_period', $targetPeriod)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    $this->line("  ✅ [{$customer->name}] Invoice {$targetPeriod} sudah ada — dilewati.");
                    continue;
                }

                // Buat invoice jika bukan dry-run
                if (!$dryRun) {
                    try {
                        $invoiceService = new InvoiceService();
                        $invoice = $invoiceService->generateForCustomer($customer, $targetPeriod);

                        if ($invoice) {
                            $generated++;
                            $this->info("  🧾 [{$customer->name}] Invoice {$targetPeriod} BERHASIL dibuat (JT: {$invoice->due_date->format('d-m-Y')})");
                            Log::info("[GenerateEmergencyInvoices] Invoice dibuat: {$customer->name} | Periode: {$targetPeriod}");
                        } else {
                            $skipped++;
                            $this->warn("  ⚠  [{$customer->name}] Gagal dibuat (mungkin sudah ada).");
                        }
                    } catch (\Exception $e) {
                        $this->error("  ❌ [{$customer->name}] Error: " . $e->getMessage());
                        Log::error("[GenerateEmergencyInvoices] Error {$customer->name}: " . $e->getMessage());
                    }
                } else {
                    $generated++;
                    $this->info("  🧾 [DRY-RUN] [{$customer->name}] Akan dibuat invoice periode: {$targetPeriod} (JT: {$dueDateThisMonth->format('d')}-{$targetPeriod})");
                }
            }

            $this->line('');
        }

        $this->info('═══════════════════════════════════════════');
        $this->info("✔  Selesai! Dibuat: {$generated} | Dilewati: {$skipped}");
        $this->info('═══════════════════════════════════════════');
    }
}
