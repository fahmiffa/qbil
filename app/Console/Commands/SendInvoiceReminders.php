<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\AppSetting;
use App\Services\WhatsappService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendInvoiceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:remind';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim pengingat tagihan via WhatsApp berdasarkan setting notifikasi';

    /**
     * Execute the console command.
     */
    public function handle(WhatsappService $whatsappService)
    {
        $this->info('Memulai pengecekan pengingat tagihan...');

        // Get all unpaid invoices
        $invoices = Invoice::with(['customer.user', 'customer.package', 'customer.user.appSetting'])
            ->where('status', 'unpaid')
            ->get();

        $sentCount = 0;

        foreach ($invoices as $invoice) {
            $customer = $invoice->customer;
            $user = $customer->user;
            $appSetting = $user->appSetting;

            if (!$appSetting || !$appSetting->notif || !$appSetting->template) {
                continue;
            }

            // Target reminder date = Customer Due Date - NOTIF days
            // Note: Assuming customer->due_date for the CURRENT billing cycle
            // If customer->due_date is just a day of the month, we might need different logic.
            // But following the user's instruction specifically "field due date di model customer"
            if (!$customer->due_date) {
                continue;
            }

            $dueDate = Carbon::parse($customer->due_date);
            $reminderDate = $dueDate->copy()->subDays((int) $appSetting->notif);

            // If today is the reminder date
            if (now()->isSameDay($reminderDate)) {
                $this->info("Mengirim pengingat untuk {$customer->name}...");

                $message = $whatsappService->formatMessage($appSetting->template, [
                    'name' => $customer->name,
                    'invoice_number' => $invoice->invoice_number,
                    'amount' => $invoice->amount,
                    'unique_code' => $invoice->unique_code,
                    'total_amount' => $invoice->total_amount,
                    'period' => $invoice->billing_period,
                    'due_date' => $invoice->due_date->format('d-m-Y'),
                    'package' => $customer->package->name ?? '-',
                ]);

                $success = $whatsappService->sendMessage(
                    $user->phone ?? '', 
                    $customer->phone, 
                    $message
                );

                if ($success) {
                    $sentCount++;
                }
            }
        }

        $this->info("Selesai. Total pengingat dikirim: $sentCount");
    }
}
