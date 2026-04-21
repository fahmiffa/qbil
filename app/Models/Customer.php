<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_pelanggan', 'name', 'phone', 'address', 'keterangan', 'status', 'due_date', 'user_id',
        'package_id', 'ppp_profile', 'username', 'password', 'service_type', 'ip_address', 'mac_address', 'dhcp_server', 'creation_method', 
        'activated_at', 'latitude', 'longitude', 'isolated_at'
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'isolated_at' => 'datetime',
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

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    protected static function booted()
    {
        static::deleting(function ($customer) {
            // Hapus semua invoice terkait saat pelanggan dihapus
            $customer->invoices()->delete();
        });
    }
}
