<?php

namespace App\Http\Controllers;

use App\Jobs\RebootOnuJob;
use App\Models\Olt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OltSseController extends Controller
{
    /**
     * Timeout in seconds for HTTP request to OLT.
     * OLT page is heavy (~100KB+), so we allow up to 60s.
     */
    private const HTTP_TIMEOUT = 60;

    /**
     * Cache TTL in seconds — keep last successful data for 5 minutes
     * so timeouts serve stale data instead of errors.
     */
    private const CACHE_TTL = 300;

    /**
     * SSE refresh interval in seconds.
     */
    private const REFRESH_INTERVAL = 30;

    /**
     * Stream ONU data from OLT via Server-Sent Events.
     * Refreshes every REFRESH_INTERVAL seconds.
     */
    public function stream(Request $request): StreamedResponse
    {
        $oltId = $request->query('olt_id');

        return response()->stream(function () use ($oltId) {
            set_time_limit(0);
            ini_set('max_execution_time', 0);

            // Max ~1 hour of streaming
            $maxIterations = (int) (3600 / self::REFRESH_INTERVAL);
            $iteration     = 0;

            while ($iteration < $maxIterations) {
                if (connection_aborted()) {
                    break;
                }

                $data = $this->fetchOnuData($oltId);

                echo "event: onu-update\n";
                echo "data: " . json_encode($data) . "\n\n";

                ob_flush();
                flush();

                $iteration++;

                // Wait REFRESH_INTERVAL but bail early if client disconnects.
                // Use 1s chunks so connection_aborted() is checked more frequently.
                if ($iteration < $maxIterations) {
                    for ($i = 0; $i < self::REFRESH_INTERVAL; $i++) {
                        if (connection_aborted()) break 2;
                        sleep(1);
                    }
                }
            }

            // Closing heartbeat
            echo "event: close\n";
            echo "data: {}\n\n";
            ob_flush();
            flush();

        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
        ]);
    }

    /**
     * Fetch and parse ONU list from OLT device.
     * Uses Laravel Cache so timeout returns stale data instead of error.
     */
    private function fetchOnuData(?string $oltId): array
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

            // Save to cache on success
            Cache::put($cacheKey, $payload, self::CACHE_TTL);

            return $payload;

        } catch (\Exception $e) {
            // Try to serve cached (stale) data with a warning
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

    /**
     * Parse the OLT HTML page and return ONU rows.
     * Handles both single-quoted and escaped values.
     */
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

        // Unescape any backslash-escaped quotes inside values
        $values = array_map(
            fn($v) => stripslashes($v),
            $items[1]
        );

        return array_chunk($values, 22);
    }

    /**
     * Build a standardised error response.
     */
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

    /**
     * Convert raw cURL / HTTP error messages to human-readable Indonesian.
     */
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

    /**
     * Dispatch Job to reboot ONU
     */
    public function rebootOnu(Request $request)
    {
        $request->validate([
            'olt_id'   => 'required|integer',
            'onu_id'   => 'required|string',
            'onu_name' => 'required|string',
        ]);

        RebootOnuJob::dispatch(
            $request->olt_id,
            $request->onu_id,
            $request->onu_name
        );

        return response()->json(['success' => true, 'message' => 'Perintah reboot telah dikirim dan sedang diproses di latar belakang.']);
    }
}
