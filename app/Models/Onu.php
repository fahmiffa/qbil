<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Onu extends Model
{
    protected $table = 'onu_table';

    public function statusHistory()
    {
        return $this->hasMany(OnuStatusHistory::class, 'onu_id', 'id');
    }
}