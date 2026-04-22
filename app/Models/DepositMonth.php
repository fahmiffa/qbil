<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
 
class DepositMonth extends Model
{
    use HasFactory;

    protected $fillable = [
        'deposit_id',
        'month',
    ];

    protected $casts = [
        'month' => 'date',
    ];

    public function deposit()
    {
        return $this->belongsTo(Deposit::class);
    }
}
