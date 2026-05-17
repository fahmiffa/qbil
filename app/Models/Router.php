<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Router extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'host', 'port', 'username', 'password',
        'connection_status', 'ping_ms', 'connection_error', 'last_checked_at',
    ];

    protected $casts = [
        'last_checked_at' => 'datetime',
        'ping_ms' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function packages()
    {
        return $this->hasMany(Package::class);
    }
}
