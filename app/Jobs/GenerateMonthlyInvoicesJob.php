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
     * Unique key — job dengan periode yang sama tidak bisa masuk queue dua kali
     * selama job pertama masih pending/running.
     */
    public function uniqueId(): string
    {
        return 'generate-invoices-' . $this->period;
    }

    /**
     * Durasi lock unique dalam detik (10 menit).
     * Setelah job selesai atau timeout, lock dilepas otomatis.
     */
    public function uniqueFor(): int
    {
        return 600;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsappService $whatsappService): void
    {
        Log::info("[GenerateMonthlyInvoicesJob] Mulai generate invoice untuk periode: {$this->period}");

        $totalGenerated = 0;

        $users = User::all();

        foreach ($users as $user) {
            $customers = Customer::where('user_id', $user->id)
                ->where('status', 'active')
                ->whereNotNull('package_id')
                ->get();

            if ($customers->isEmpty()) {
                continue;
            }

            foreach ($customers as $customer) {

                try {
                    DB::transaction(function () use ($customer, $user, &$totalGenerated) {

                        $amount = $customer->package->price ?? 0;

                        // =========================
                        // 1. UNIQUE CODE GENERATION
                        // =========================
                        $base = $customer->id . $this->period;

                        $uniqueCode = hexdec(substr(md5($base), 0, 8)) % 1000;

                        // safety format 3 digit
                        $uniqueCode = str_pad($uniqueCode, 3, '0', STR_PAD_LEFT);

                        // =========================
                        // 2. INVOICE NUMBER SAFE SEQUENCE
                        // =========================
                        $periodSlug = str_replace('-', '', $this->period);

                        $seq = Invoice::where('customer_id', $customer->id)
                            ->where('billing_period', $this->period)
                            ->lockForUpdate()
                            ->count() + 1;

                        $invoiceNumber = 'INV-' . $periodSlug . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);

                        // =========================
                        // 3. CREATE (DB IS GUARDIAN)
                        // =========================
                        Invoice::create([
                            'id'             => (string) Str::uuid(),
                            'customer_id'    => $customer->id,
                            'package_id'     => $customer->package_id,
                            'invoice_number' => $invoiceNumber,
                            'amount'         => $amount,
                            'unique_code'    => $uniqueCode,
                            'total_amount'   => $amount + (int)$uniqueCode,
                            'billing_period' => $this->period,
                            'status'         => 'unpaid',
                            'due_date'       => $customer->due_date,
                        ]);

                        $totalGenerated++;
                    });
                } catch (\Illuminate\Database\QueryException $e) {

                    // =========================
                    // DUPLICATE SAFE HANDLING
                    // =========================
                    if ($e->getCode() == 23000) {
                        // duplicate → skip silently
                        continue;
                    }

                    Log::error($e->getMessage());
                }
            }
        }

        Log::info("[GenerateMonthlyInvoicesJob] Selesai. Total invoice dibuat: {$totalGenerated}");
    }
}
