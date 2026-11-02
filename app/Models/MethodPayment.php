<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MethodPayment extends Model
{
    protected $table = 'method_payment';
    protected $fillable = ['user_id', 'nama'];
}
