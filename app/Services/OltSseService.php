<?php

namespace App\Services;

use App\Jobs\RebootOnuJob;
use App\Models\Olt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class OltSseService
{
    private const HTTP_TIMEOUT = 60;
    private const CACHE_TTL = 300;

    /**
     * Fetch and parse ONU list from OLT device.
     */
    public function fetchOnuData(?string $oltId): array
    {
        // Resolve OLT device
        $olt = $oltId ? Olt::find($oltId) : Olt::first();

        if (!$olt) {
            return $this->errorResponse('OLT tidak ditemukan. Silakan tambahkan perangkat OLT terlebih dahulu.');
        }

        $cacheKey = "olt_onu_data_{$olt->id}";

        try {
            $response = Http::withBasicAuth($olt->username, $olt->password)
                ->timeout(self::HTTP_TIMEOUT)
                ->get($olt->ip . '/onuAllPonOnuList.asp');

            if (!$response->successful()) {
                throw new \Exception("HTTP {$response->status()} dari OLT");
            }

            $html    = $response->body();
            $onuList = $this->parseOnuHtml($html);

            $online  = collect($onuList)->filter(fn($r) => ($r[3] ?? '') === 'Up')->count();
            $offline = count($onuList) - $online;

            $payload = [
                'success'  => true,
                'stale'    => false,
                'onuList'  => $onuList,
                'oltName'  => $olt->name,
                'oltIp'    => $olt->ip,
                'stats'    => [
                    'total'   => count($onuList),
                    'online'  => $online,
                    'offline' => $offline,
                ],
            ];

            Cache::put($cacheKey, $payload, self::CACHE_TTL);

            return $payload;

        } catch (\Exception $e) {
            $cached = Cache::get($cacheKey);

            if ($cached) {
                $cached['stale']         = true;
                $cached['stale_message'] = 'Menggunakan data terakhir (OLT lambat merespons).';
                return $cached;
            }

            return $this->errorResponse(
                'Gagal terhubung ke OLT. ' . $this->humanizeError($e->getMessage())
            );
        }
    }

    private function parseOnuHtml(string $html): array
    {
        if (!preg_match('/var\s+onutable\s*=\s*new\s+Array\((.*?)\);/is', $html, $matches)) {
            return [];
        }

        $arrayContent = $matches[1];
        preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $arrayContent, $items);

        if (empty($items[1])) {
            return [];
        }

        $values = array_map(
            fn($v) => stripslashes($v),
            $items[1]
        );

        return array_chunk($values, 22);
    }

    private function errorResponse(string $message): array
    {
        return [
            'success' => false,
            'stale'   => false,
            'message' => $message,
            'onuList' => [],
            'stats'   => ['total' => 0, 'online' => 0, 'offline' => 0],
            'oltName' => null,
        ];
    }

    private function humanizeError(string $raw): string
    {
        if (str_contains($raw, 'timed out') || str_contains($raw, 'Operation timed out')) {
            return 'Perangkat OLT memerlukan waktu lama untuk merespons. Coba lagi sebentar.';
        }
        if (str_contains($raw, 'Connection refused')) {
            return 'Koneksi ditolak — pastikan IP OLT benar dan perangkat aktif.';
        }
        if (str_contains($raw, 'Could not resolve host')) {
            return 'Host OLT tidak dapat ditemukan — periksa IP/hostname OLT.';
        }
        return $raw;
    }

    public function rebootOnu($oltId, $onuId, $onuName): void
    {
        RebootOnuJob::dispatch($oltId, $onuId, $onuName);
    }
}
