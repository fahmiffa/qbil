<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'price', 'speed_download', 'speed_upload', 'mikrotik_profile',
        'user_id', 'tipe', 'limit_time', 'masa_aktif', 'valid_duration', 'address_pool',
    ];

    /**
     * Konversi string durasi MikroTik (contoh: "1d", "12h", "30d") ke detik.
     */
    public function getMasaAktifSeconds(): int
    {
        return self::parseDurationToSeconds($this->masa_aktif ?? '');
    }

    public function getValidDurationSeconds(): int
    {
        return self::parseDurationToSeconds($this->valid_duration ?? '');
    }

    public static function parseDurationToSeconds(string $duration): int
    {
        if (empty($duration)) return 0;
        $total = 0;
        preg_match_all('/([\d.]+)([wdhms])/i', $duration, $matches, PREG_SET_ORDER);
        foreach ($matches as $m) {
            $v = (float) $m[1];
            switch (strtolower($m[2])) {
                case 'w': $total += (int)($v * 604800); break;
                case 'd': $total += (int)($v * 86400);  break;
                case 'h': $total += (int)($v * 3600);   break;
                case 'm': $total += (int)($v * 60);     break;
                case 's': $total += (int)$v;            break;
            }
        }
        return $total;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
