<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PppProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'router_id',
        'name',
        'local_address',
        'remote_address',
        'rate_limit',
        'only_one',
        'dns_server',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function router()
    {
        return $this->belongsTo(Router::class);
    }
}
