<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnuStatusHistory extends Model
{
    protected $table = 'onu_status_history';
    public $timestamps = false;
    protected $guarded = [];

    // Each history row belongs to an ONU (view_onu)
    public function onu()
    {
        return $this->belongsTo(ViewOnu::class, 'onu_id');
    }
}
