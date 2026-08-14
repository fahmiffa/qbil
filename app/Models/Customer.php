<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_pelanggan',
        'name',
        'phone',
        'phone2',
        'address',
        'keterangan',
        'status',
        'due_date',
        'user_id',
        'router_id',
        'package_id',
        'ppp_profile',
        'username',
        'password',
        'service_type',
        'ip_address',
        'mac_address',
        'dhcp_server',
        'creation_method',
        'activated_at',
        'latitude',
        'longitude',
        'isolated_at',
        'asset_id',
        'wa_notify',
        'unique_code'
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'isolated_at' => 'datetime',
        'due_date' => 'date',
        'wa_notify' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function router()
    {
        return $this->belongsTo(Router::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function viewOnu()
    {
        return $this->hasOne(ViewOnu::class, 'name', 'name')
            ->orWhere('mac_address', $this->mac_address);
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    public function piutangs()
    {
        return $this->hasMany(Piutang::class);
    }

    protected static function booted()
    {
        static::deleting(function ($customer) {
            // Hapus semua invoice, deposit, dan piutang terkait saat pelanggan dihapus
            $customer->invoices()->delete();
            $customer->deposits()->delete();
            $customer->piutangs()->delete();
        });
    }
}
