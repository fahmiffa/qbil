<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RebootSchedule extends Model
{
    protected $fillable = [
        'user_id',
        'interval_days',
        'time',
        'next_run_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
