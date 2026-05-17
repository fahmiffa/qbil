<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'price', 'speed_download', 'speed_upload', 'mikrotik_profile',
        'user_id', 'router_id', 'tipe', 'limit_time', 'masa_aktif', 'valid_duration', 'address_pool',
        'burst_upload', 'burst_download', 'burst_threshold', 'limit_at', 'burst_duration', 'priority',
    ];

    protected $casts = [
        'price' => 'integer',
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

    public function router()
    {
        return $this->belongsTo(Router::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Generate MikroTik rate-limit string.
     * Format: max-limit [burst-limit [burst-threshold [burst-time [priority [limit-at]]]]]
     */
    public function getMikrotikRateLimit(): string
    {
        $maxLimit = ($this->speed_upload ?: '0') . '/' . ($this->speed_download ?: '0');
        
        if (empty($this->burst_upload) && empty($this->burst_download)) {
            return $maxLimit;
        }

        $burstLimit = ($this->burst_upload ?: $this->speed_upload) . '/' . ($this->burst_download ?: $this->speed_download);
        
        // Threshold
        $thresholdUpload = '0';
        $thresholdDownload = '0';
        if ($this->burst_threshold) {
            $thresholdUpload = $this->calculatePercentage($this->speed_upload, $this->burst_threshold);
            $thresholdDownload = $this->calculatePercentage($this->speed_download, $this->burst_threshold);
        }
        $burstThreshold = $thresholdUpload . '/' . $thresholdDownload;

        $burstTime = ($this->burst_duration ?: '1') . '/' . ($this->burst_duration ?: '1');
        $priority = $this->priority ?: '8';

        // Limit At
        $limitAtUpload = '0';
        $limitAtDownload = '0';
        if ($this->limit_at) {
            $limitAtUpload = $this->calculatePercentage($this->speed_upload, $this->limit_at);
            $limitAtDownload = $this->calculatePercentage($this->speed_download, $this->limit_at);
        }
        $limitAt = $limitAtUpload . '/' . $limitAtDownload;

        return "{$maxLimit} {$burstLimit} {$burstThreshold} {$burstTime} {$priority} {$limitAt}";
    }

    private function calculatePercentage($value, $percentage): string
    {
        if (!$value) return '0';
        if (preg_match('/^(\d+)(K|M)$/i', $value, $matches)) {
            $val = (int)$matches[1];
            $unit = strtoupper($matches[2]);
            
            // Convert everything to K for precision
            $valK = ($unit === 'M') ? $val * 1024 : $val;
            $resK = floor($valK * ($percentage / 100));
            
            return $resK . 'k';
        }
        return '0';
    }
}
