<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Piutang extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'customer_id',
        'invoice_id',
        'user_id',
        'amount',
        'billing_period',
        'status',
        'notes',
        'paid_at',
        'payment_method'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::updated(function ($piutang) {
            if ($piutang->wasChanged('status') && $piutang->status === 'paid') {
                $exists = Transaction::where('reference_type', self::class)
                    ->where('reference_id', $piutang->id)
                    ->exists();

                if (!$exists) {
                    Transaction::create([
                        'user_id' => $piutang->user_id,
                        'type' => 'income',
                        'amount' => $piutang->amount,
                        'category' => 'Pelunasan Piutang',
                        'description' => 'Pelunasan piutang dari invoice ' . ($piutang->invoice->invoice_number ?? $piutang->billing_period) . ($piutang->payment_method ? ' via ' . $piutang->payment_method : ''),
                        'reference_type' => self::class,
                        'reference_id' => $piutang->id,
                        'transaction_date' => $piutang->paid_at ?? now(),
                        'payment_method' => $piutang->payment_method,
                        'service_type' => $piutang->customer->package->tipe ?? null,
                    ]);
                }
            }
        });
    }
}
