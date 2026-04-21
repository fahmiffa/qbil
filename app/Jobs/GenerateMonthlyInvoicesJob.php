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
        $currentHour = $now->format('H');

        $users = User::with('appSetting')->get();

        foreach ($users as $user) {
            $setting = $user->appSetting;

            if (!$setting) {
                continue;
            }

            // Cek apakah jam sekarang sesuai dengan konfigurasi jam eksekusi
            $configHour = Carbon::parse($setting->invoice_gen_time)->format('H');
            if ($currentHour != $configHour) {
                continue;
            }

            $offsetDays = (int) $setting->invoice_gen_days;

            $customers = Customer::where('user_id', $user->id)
                ->where('status', 'active')
                ->whereNotNull('package_id')
                ->whereNotNull('due_date')
                ->get();

            if ($customers->isEmpty()) {
                continue;
            }

            foreach ($customers as $customer) {
                // Cek apakah hari ini adalah tanggal eksekusi (due_date + offset)
                $originalDueDate = Carbon::parse($customer->due_date);
                $targetDate = $originalDueDate->copy()->addDays($offsetDays);

                if (!$now->isSameDay($targetDate)) {
                    continue;
                }

                // Tentukan periode billing
                $targetPeriod = $this->period ?: $originalDueDate->format('Y-m');

                try {
                    $invoiceService = new \App\Services\InvoiceService();
                    $invoice = $invoiceService->generateForCustomer($customer, $targetPeriod);

                    if ($invoice) {
                        $totalGenerated++;
                    }
                } catch (\Exception $e) {
                    Log::error("GenerateMonthlyInvoicesJob Error for customer {$customer->id}: " . $e->getMessage());
                }
            }
        }

        Log::info("[GenerateMonthlyInvoicesJob] Selesai. Total invoice dibuat: {$totalGenerated}");
    }
}
