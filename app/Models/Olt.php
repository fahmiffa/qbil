<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Olt extends Model
{
    protected $fillable = ['user_id', 'name', 'ip', 'username', 'password'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
