<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use App\Services\WhatsappService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GenerateMonthlyInvoicesJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan ulang jika job gagal.
     * Set 1 karena job bersifat idempotent — jika gagal investigasi dari log,
     * daripada retry otomatis yang bisa menyebabkan efek samping ganda.
     */
    public int $tries = 1;

    /**
     * Timeout job dalam detik.
     */
    public int $timeout = 300;

    public function __construct(public readonly string $period) {}

    /**
     * Unique key — dibuat unik per jam agar pengecekan setiap jam
     * tidak saling memblokir.
     */
    public function uniqueId(): string
    {
        return 'generate-invoices-' . $this->period . '-' . now()->format('Y-m-d-H');
    }

    /**
     * Durasi lock unique dalam detik (10 menit).
     * Setelah job selesai atau timeout, lock dilepas otomatis.
     */
    public function uniqueFor(): int
    {
        return 600;
    }

    public function handle(WhatsappService $whatsappService): void
    {
        Log::info("[GenerateMonthlyInvoicesJob] Mulai pengecekan generate invoice otomatis...");

        $totalGenerated = 0;
        $now = now();

        $users = User::with('appSetting')->get();

        foreach ($users as $user) {
            $setting = $user->appSetting;

            if (!$setting) {
                continue;
            }

            // Cek apakah waktu sekarang sesuai dengan konfigurasi waktu eksekusi (Jam & Menit)
            $configTime = Carbon::parse($setting->invoice_gen_time)->format('H:i');
            if ($now->format('H:i') !== $configTime) {
                continue;
            }

            $offsetDays = (int) $setting->invoice_gen_days;

            $customers = Customer::where('user_id', $user->id)
                ->where('status', 'active')
                ->whereNotNull('package_id')
                ->whereNotNull('due_date')
                ->get();

            $userGenerated = 0;
            $lastTargetPeriod = '';

            if ($customers->isEmpty()) {
                continue;
            }

            foreach ($customers as $customer) {
                // Cek apakah hari ini adalah tanggal eksekusi (berdasarkan hari jatuh tempo + offset)
                $originalDueDate = Carbon::parse($customer->due_date);
                $dueDay = $originalDueDate->format('d');
                
                // Hitung kapan jatuh tempo seharusnya jika invoice digenerate hari ini
                $calculatedDueDate = $now->copy()->subDays($offsetDays);
                
                if ($calculatedDueDate->format('d') !== $dueDay) {
                    continue;
                }
                
                // Tentukan periode billing berdasarkan tanggal jatuh tempo yang dihitung
                $targetPeriod = $this->period ?: $calculatedDueDate->format('Y-m');
                $lastTargetPeriod = $targetPeriod;
                
                Log::info("[customer: {$user->name} {$customer->name} {$customer->due_date}  {$targetPeriod}]");
                
                try {
                    $invoiceService = new \App\Services\InvoiceService();
                    $invoice = $invoiceService->generateForCustomer($customer, $targetPeriod);

                    if ($invoice) {
                        $totalGenerated++;
                        $userGenerated++;

                        // Notify admin about each generated invoice
                        $user->notify(new \App\Notifications\SystemReportNotification(
                            'Tagihan Dibuat',
                            "Tagihan otomatis berhasil dibuat untuk pelanggan: {$customer->name} (Periode: {$targetPeriod}).",
                            'invoice'
                        ));
                    }
                } catch (\Exception $e) {
                    Log::error("[GenerateMonthlyInvoicesJob] Gagal generate invoice untuk {$customer->name}: " . $e->getMessage());
                }
            }
        }

        Log::info("[GenerateMonthlyInvoicesJob] Selesai. Total invoice dibuat: {$totalGenerated}");
    }
}
