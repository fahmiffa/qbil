<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\OnuStatusHistory;

class ViewOnu extends Model
{
    protected $table = 'view_onu';

    public function statusHistory()
    {
        return $this->hasMany(OnuStatusHistory::class, 'onu_id');
    }

    public function onu()
    {
        return $this->hasOne(Onu::class, 'id', 'id');
    }
}
