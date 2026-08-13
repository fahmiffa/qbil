<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OltApiService
{
    private string $baseUrl;
    private const HTTP_TIMEOUT = 15;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.olt.url', env('OLT_URL', '')), '/');
    }

    /**
     * Fetch ONU detail information from the OLT HTTP API by ONU name.
     *
     * @param  string $onuName  The ONU name, e.g. "TARMUDI / WIDIAWATI"
     * @return array|null       First ONU data array, or null on failure
     */
    public function fetchOnuByName(string $onuName): ?array
    {
        if (empty($this->baseUrl)) {
            Log::warning('OltApiService: OLT_URL is not configured.');
            return null;
        }

        try {
            $response = Http::timeout(self::HTTP_TIMEOUT)
                ->get("{$this->baseUrl}/api/onu", [
                    'name' => $onuName,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (is_array($data) && count($data) > 0) {
                    return $data[0];
                }
            } else {
                Log::warning("OltApiService: API returned HTTP {$response->status()} for ONU name [{$onuName}]");
            }
        } catch (\Exception $e) {
            Log::error("OltApiService: Failed to fetch ONU [{$onuName}] — {$e->getMessage()}");
        }

        return null;
    }
}
