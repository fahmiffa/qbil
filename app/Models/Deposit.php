<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Deposit extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'customer_id', 'package_id', 'user_id', 'amount_per_month', 'months_count', 
        'used_months', 'total_amount', 'status', 'notes', 'payment_date',
        'start_date', 'end_date'
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'amount_per_month' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function months()
    {
        return $this->hasMany(DepositMonth::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
