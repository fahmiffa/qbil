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
}
