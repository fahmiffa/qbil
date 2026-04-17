<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_pelanggan', 'name', 'phone', 'address', 'keterangan', 'status', 'due_date', 'user_id',
        'package_id', 'username', 'password', 'service_type', 'ip_address', 'activated_at',
        'latitude', 'longitude'
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'due_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
