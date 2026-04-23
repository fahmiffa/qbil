<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\AppSetting;
use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendManualInvoiceWhatsappJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $invoice;

    /**
     * Create a new job instance.
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsappService $whatsappService): void
    {
        // Refresh invoice
        $invoice = $this->invoice->fresh(['customer.package', 'customer.user']);
        
        if (!$invoice || !$invoice->customer) {
            Log::warning("[SendManualInvoiceWhatsappJob] Invoice atau Customer tidak ditemukan.");
            return;
        }

        $customer = $invoice->customer;
        $user = $customer->user;

        if (!$customer->phone) {
            Log::warning("[SendManualInvoiceWhatsappJob] Nomor telepon tidak ada untuk customer: {$customer->name}");
            return;
        }

        $appSetting = AppSetting::where('user_id', $user->id)->first();
        if (!$appSetting) {
            Log::warning("[SendManualInvoiceWhatsappJob] AppSetting tidak ditemukan untuk user_id: {$user->id}");
            return;
        }

        $templateText = '';
        if ($invoice->status === 'paid' || $invoice->status === 'unpaid_piutang' || $invoice->status === 'paid_piutang') {
            // For paid invoices (including piutang that are marked as paid), we send payment template
            // Actually, if it's unpaid piutang, they haven't paid yet, maybe send normal template?
            // Let's keep it simple: if 'paid', use payment_template. Else, use template.
            if ($invoice->status === 'paid') {
                $templateText = $appSetting->payment_template ?? '';
            } else {
                $templateText = $appSetting->template ?? '';
            }
        } else {
            $templateText = $appSetting->template ?? '';
        }

        if (empty($templateText)) {
            Log::warning("[SendManualInvoiceWhatsappJob] Template pesan WhatsApp kosong.");
            return;
        }

        Log::info("[SendManualInvoiceWhatsappJob] Mengirim pesan manual untuk {$customer->name} (Status: {$invoice->status})...");

        $publicUrl = route('public.invoice', ['invoice' => $invoice->id]);

        $message = $whatsappService->formatMessage($templateText, [
            'name'           => $customer->name,
            'invoice_number' => $invoice->invoice_number,
            'amount'         => $invoice->amount,
            'unique_code'    => $invoice->unique_code,
            'total_amount'   => $invoice->total_amount,
            'period'         => $invoice->billing_period,
            'due_date'       => $invoice->due_date ? $invoice->due_date->format('d-m-Y') : '-',
            'package'        => $customer->package->name ?? '-',
            'id_pelanggan'   => $customer->id_pelanggan ?? '-',
            'address'        => $customer->address ?? '-',
            'package_name'   => $customer->package->name ?? '-',
            'public_url'     => $publicUrl,
        ]);

        $success = $whatsappService->sendMessage(
            $user->phone ?? '',
            $customer->phone,
            $message
        );

        if ($success) {
            Log::info("[SendManualInvoiceWhatsappJob] Sukses mengirim pesan ke {$customer->phone}.");
        } else {
            Log::error("[SendManualInvoiceWhatsappJob] Gagal mengirim pesan ke {$customer->phone}.");
        }
    }
}
