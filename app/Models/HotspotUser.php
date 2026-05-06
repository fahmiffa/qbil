<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotspotUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'username', 'password', 'profile', 'package_id',
        'voucher_order_id', 'activated_at', 'valid_until',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'valid_until'  => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function voucherOrder()
    {
        return $this->belongsTo(VoucherOrder::class);
    }
}
