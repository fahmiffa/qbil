<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappServer extends Model
{
    protected $fillable = [
        'name',
        'api_url',
        'socket_url',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
