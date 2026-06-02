<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'customer_id',
        'invoice_number',
        'amount',
        'unique_code',
        'total_amount',
        'billing_period',
        'status',
        'due_date',
        'paid_at',
        'package_id',
        'reminder_1_sent_at',
        'reminder_2_sent_at',
        'payment_method',
        'notes',
        'discount',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'reminder_1_sent_at' => 'datetime',
        'reminder_2_sent_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    protected static function booted()
    {
        static::updated(function ($invoice) {
            if ($invoice->wasChanged('status') && $invoice->status === 'paid') {
                // Ensure a transaction doesn't already exist for this invoice
                $exists = Transaction::where('reference_type', self::class)
                    ->where('reference_id', $invoice->id)
                    ->exists();

                // Check if this invoice is in Piutang
                $isPiutang = \App\Models\Piutang::where('invoice_id', $invoice->id)->exists();

                if (!$exists && !$isPiutang) {
                    Transaction::create([
                        'user_id' => $invoice->customer->user_id, // Get owner from customer
                        'type' => 'income',
                        'amount' => $invoice->total_amount,
                        'category' => 'Tagihan Bulanan',
                        'description' => 'Pembayaran tagihan ' . $invoice->billing_period . ' (' . $invoice->invoice_number . ')' . ($invoice->payment_method ? ' via ' . $invoice->payment_method : ''),
                        'reference_type' => self::class,
                        'reference_id' => $invoice->id,
                        'transaction_date' => $invoice->paid_at ?? now(),
                        'payment_method' => $invoice->payment_method,
                        'service_type' => $invoice->package->tipe ?? null,
                    ]);
                }
            }
        });
    }
}
