<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherOrder extends Model
{
    protected $fillable = [
        'order_code',
        'user_id',
        'package_id',
        'whatsapp',
        'quantity',
        'total_price',
        'unique_amount',
        'payment_status',
        'paid_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function hotspotUsers()
    {
        return $this->hasMany(HotspotUser::class);
    }

    protected static function booted()
    {
        static::updated(function ($order) {
            if ($order->wasChanged('payment_status') && $order->payment_status === 'paid') {
                $exists = Transaction::where('reference_type', self::class)
                    ->where('reference_id', $order->id)
                    ->exists();

                if (!$exists) {
                    Transaction::create([
                        'user_id' => $order->user_id,
                        'type' => 'income',
                        'amount' => $order->total_price + $order->unique_amount,
                        'category' => 'Voucher Hotspot',
                        'description' => 'Pembelian ' . $order->quantity . ' Voucher (' . $order->order_code . ')',
                        'reference_type' => self::class,
                        'reference_id' => $order->id,
                        'transaction_date' => $order->paid_at ?? now(),
                        'service_type' => 'hotspot',
                    ]);
                }
            }
        });
    }
}
