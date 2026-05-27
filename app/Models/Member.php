<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'whatsapp_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
