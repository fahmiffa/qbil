<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DhcpServer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'router_id',
        'name',
        'interface',
        'address_pool',
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
