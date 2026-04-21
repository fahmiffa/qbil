<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'template',
        'registration_template',
        'payment_instruction',
        'qr',
        'reminder_1_days',
        'reminder_1_time',
        'reminder_2_days',
        'reminder_2_time',
        'invoice_gen_days',
        'invoice_gen_time',
        'isolate_days',
        'isolate_time',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
