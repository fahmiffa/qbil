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
                // Skip jika invoice untuk periode ini sudah ada
                $exists = Invoice::where('customer_id', $customer->id)
                    ->where('billing_period', $this->period)
                    ->exists();

                if ($exists) {
                    continue;
                }

                try {
                    DB::transaction(function () use ($customer, $user, $whatsappService, &$totalGenerated) {
                        $amount  = $customer->package->price ?? 0;

                        // Due date invoice = due_date dari data customer (fallback +7 hari jika kosong)
                        $dueDate = $customer->due_date
                            ? $customer->due_date->format('Y-m-d')
                            : now()->addDays(7)->format('Y-m-d');

                        // -----------------------------------------------------------
                        // LOCK: Exclude kode yang sudah dipakai jika:
                        //   1. Invoice masih unpaid (lintas periode) — cegah ambiguitas transfer
                        //   2. Invoice dalam periode yang sama (apapun statusnya) — cegah
                        //      duplikat dalam 1 bulan meski invoice sudah paid/canceled
                        // -----------------------------------------------------------
                        $usedCodes = Invoice::whereHas('customer', function ($q) use ($user) {
                                $q->where('user_id', $user->id);
                            })
                            ->where(function ($q) {
                                $q->where('status', 'unpaid')
                                  ->orWhere('billing_period', $this->period);
                            })
                            ->lockForUpdate()  // <-- Pessimistic lock
                            ->pluck('unique_code')
                            ->toArray();

                        $availableCodes = array_values(
                            array_diff(range(1, 999), $usedCodes)
                        );

                        if (empty($availableCodes)) {
                            Log::warning("[GenerateMonthlyInvoicesJob] Kode unik penuh untuk user {$user->name}");
                            return;
                        }

                        // Pilih kode unik secara random dari yang tersedia
                        $uniqueCode  = $availableCodes[array_rand($availableCodes)];
                        $totalAmount = $amount + $uniqueCode;

                        // -----------------------------------------------------------
                        // LOCK: Kunci baris invoice_number terakhir untuk periode ini
                        // agar tidak ada 2 worker yang mengambil sequence yang sama
                        // -----------------------------------------------------------
                        $periodSlug  = str_replace('-', '', $this->period);
                        $lastInvoice = Invoice::where('invoice_number', 'like', "INV-{$periodSlug}-%")
                            ->orderBy('invoice_number', 'desc')
                            ->lockForUpdate()  // <-- Pessimistic lock
                            ->first();

                        $seq = 1;
                        if ($lastInvoice) {
                            $parts = explode('-', $lastInvoice->invoice_number);
                            $seq   = ((int) end($parts)) + 1;
                        }

                        $invoiceNumber = 'INV-' . $periodSlug . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);

                        Invoice::create([
                            'id'             => (string) Str::uuid(),
                            'customer_id'    => $customer->id,
                            'package_id'     => $customer->package_id,
                            'invoice_number' => $invoiceNumber,
                            'amount'         => $amount,
                            'unique_code'    => $uniqueCode,
                            'total_amount'   => $totalAmount,
                            'billing_period' => $this->period,
                            'status'         => 'unpaid',
                            'due_date'       => $dueDate,
                        ]);

                        $totalGenerated++;

                        // Kirim notifikasi WhatsApp awal
                        $appSetting = $user->appSetting;
                        // if ($appSetting && $appSetting->template) {
                        //     $message = $whatsappService->formatMessage($appSetting->template, [
                        //         'name'           => $customer->name,
                        //         'invoice_number' => $invoiceNumber,
                        //         'amount'         => $amount,
                        //         'unique_code'    => $uniqueCode,
                        //         'total_amount'   => $totalAmount,
                        //         'period'         => $this->period,
                        //         'due_date'       => $customer->due_date?->format('d-m-Y') ?? now()->addDays(7)->format('d-m-Y'),
                        //         'package'        => $customer->package->name ?? '-',
                        //     ]);

                        //     $whatsappService->sendMessage(
                        //         $user->phone ?? '',
                        //         $customer->phone,
                        //         $message
                        //     );
                        // }
                    });
                } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                    // Safety net: jika unique constraint di DB menangkap duplikat
                    // (misalnya unique_code atau invoice_number), log dan skip
                    Log::warning("[GenerateMonthlyInvoicesJob] Duplikat terdeteksi untuk {$customer->name}, skip. Detail: " . $e->getMessage());
                } catch (\Exception $e) {
                    Log::error("[GenerateMonthlyInvoicesJob] Error pelanggan {$customer->name}: " . $e->getMessage());
                }
            }
        }

        Log::info("[GenerateMonthlyInvoicesJob] Selesai. Total invoice dibuat: {$totalGenerated}");
    }
}
