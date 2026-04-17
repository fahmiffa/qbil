<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\WhatsappService;


class GenerateMonthlyInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:generate {period?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate otomatis tagihan bulanan untuk seluruh pelanggan aktif';

    /**
     * Execute the console command.
     */
    public function handle(WhatsappService $whatsappService)
    {
        $period = $this->argument('period') ?: now()->format('Y-m');

        $this->info("Memulai generate invoice untuk periode: $period");

        $users = User::all();
        $totalGenerated = 0;

        foreach ($users as $user) {
            $customers = Customer::where('user_id', $user->id)
                ->where('status', 'active')
                ->whereNotNull('package_id')
                ->get();

            if ($customers->isEmpty()) continue;

            $this->info("Memproses " . $customers->count() . " pelanggan untuk user: {$user->name}");

            foreach ($customers as $customer) {
                // Check if invoice already exists
                $exists = Invoice::where('customer_id', $customer->id)
                    ->where('billing_period', $period)
                    ->exists();

                if ($exists) continue;

                try {
                    DB::transaction(function () use ($customer, $period, $user, &$totalGenerated) {
                        $amount = $customer->package->price ?? 0;
                        
                        // Find unique code for this specific user's unpaid invoices
                        $usedCodes = Invoice::whereHas('customer', function($q) use ($user) {
                                $q->where('user_id', $user->id);
                            })
                            ->where('status', 'unpaid')
                            ->pluck('unique_code')
                            ->toArray();

                        $availableCodes = array_diff(range(1, 999), $usedCodes);

                        if (empty($availableCodes)) {
                            $this->error("Gagal: Kode unik penuh untuk user {$user->name}");
                            return;
                        }

                        $uniqueCode = $availableCodes[array_rand($availableCodes)];

                        $totalAmount = $amount + $uniqueCode;
                        
                        // Invoice Number logic
                        $lastInvoice = Invoice::where('invoice_number', 'like', 'INV-' . str_replace('-', '', $period) . '-%')
                            ->orderBy('invoice_number', 'desc')
                            ->first();
                        
                        $seq = 1;
                        if ($lastInvoice) {
                            $parts = explode('-', $lastInvoice->invoice_number);
                            $seq = ((int) end($parts)) + 1;
                        }
                        $invoiceNumber = 'INV-' . str_replace('-', '', $period) . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);

                        Invoice::create([
                            'id' => (string) Str::uuid(),
                            'customer_id' => $customer->id,
                            'package_id' => $customer->package_id,
                            'invoice_number' => $invoiceNumber,
                            'amount' => $amount,
                            'unique_code' => $uniqueCode,
                            'total_amount' => $totalAmount,
                            'billing_period' => $period,
                            'status' => 'unpaid',
                            'due_date' => now()->addDays(7)->format('Y-m-d'), 
                        ]);

                        $totalGenerated++;

                        // Send Initial notification
                        $appSetting = $user->appSetting;
                        if ($appSetting && $appSetting->template) {
                            $message = $whatsappService->formatMessage($appSetting->template, [
                                'name' => $customer->name,
                                'invoice_number' => $invoiceNumber,
                                'amount' => $amount,
                                'unique_code' => $uniqueCode,
                                'total_amount' => $totalAmount,
                                'period' => $period,
                                'due_date' => now()->addDays(7)->format('d-m-Y'),
                                'package' => $customer->package->name ?? '-',
                            ]);

                            $whatsappService->sendMessage(
                                $user->phone ?? '', 
                                $customer->phone, 
                                $message
                            );
                        }

                    });
                } catch (\Exception $e) {
                    $this->error("Error pada pelanggan {$customer->name}: " . $e->getMessage());
                }
            }
        }

        $this->info("Berhasil men-generate $totalGenerated invoice baru.");
    }
}
