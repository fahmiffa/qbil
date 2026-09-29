<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    protected string $defaultUrl = 'https://gowa.qlabcode.com';

    /**
     * Check if a number is registered on WhatsApp.
     *
     * @param string $senderPhone
     * @param string $targetPhone
     * @return bool
     */
    public function checkNumber(string $senderPhone, string $targetPhone): bool
    {
        // GoWA usually has an endpoint for check number, but we'll leave this as is or adapt if needed.
        // For now, retaining the old logic for backward compatibility.
        $baseApiUrl = $this->defaultUrl;

        $user = \App\Models\User::where('phone', $senderPhone)->with('whatsappServer')->first();
        if ($user && $user->whatsappServer && !empty($user->whatsappServer->api_url)) {
            $baseApiUrl = $user->whatsappServer->api_url;
        }

        $apiUrl = rtrim($baseApiUrl, '/') . '/number';

        if (str_starts_with($targetPhone, '0')) {
            $targetPhone = '62' . substr($targetPhone, 1);
        }

        try {
            $response = Http::timeout(10)->post($apiUrl, [
                'number' => $senderPhone,
                'to' => $targetPhone
            ]);

            return $response->successful() && $response->json('status') === true;
        } catch (\Exception $e) {
            Log::error("[WhatsappService] checkNumber failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send WhatsApp message.
     */
    public function sendMessage(string $senderPhone, string $receiverPhone, string $message): bool
    {
        $baseApiUrl = $this->defaultUrl;

        $user = \App\Models\User::where('phone', $senderPhone)->with('whatsappServer')->first();
        if ($user && $user->whatsappServer && !empty($user->whatsappServer->api_url)) {
            $baseApiUrl = $user->whatsappServer->api_url;
        }

        $apiUrl = rtrim($baseApiUrl, '/') . '/send/message';

        // Format receiver phone (replace leading 0 with 62)
        if (str_starts_with($receiverPhone, '0')) {
            $receiverPhone = '62' . substr($receiverPhone, 1);
        }

        // Add the whatsapp suffix required by GoWA
        if (!str_ends_with($receiverPhone, '@s.whatsapp.net')) {
            $receiverPhone = $receiverPhone . '@s.whatsapp.net';
        }

        try {
            $response = Http::withBasicAuth('ffa', 'qqffa')
                ->withHeaders([
                    'X-Device-Id' => $senderPhone,
                ])
                ->post($apiUrl, [
                    'phone' => $receiverPhone,
                    'message' => $message,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error("[WhatsappService] API failed (URL: {$apiUrl}): " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("[WhatsappService] Exception (URL: {$apiUrl}): " . $e->getMessage());
            return false;
        }
    }

    public function formatMessage(string $template, array $data): string
    {
        $placeholders = [
            '{name}' => $data['name'] ?? '',
            '{invoice_number}' => $data['invoice_number'] ?? '',
            '{amount}' => number_format($data['amount'] ?? 0, 0, ',', '.'),
            '{unique_code}' => $data['unique_code'] ?? '',
            '{total_amount}' => number_format($data['total_amount'] ?? 0, 0, ',', '.'),
            '{period}' => $data['period'] ?? '',
            '{due_date}' => $data['due_date'] ?? '',
            '{package}' => $data['package'] ?? '',
            '{id_pelanggan}' => $data['id_pelanggan'] ?? '',
            '{address}' => $data['address'] ?? '',
            '{package_name}' => $data['package_name'] ?? ($data['package'] ?? ''),
            '{public_url}' => $data['public_url'] ?? '',
            '{user_name}' => $data['user_name'] ?? '',
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $template);
    }

    // --- GoWA API Integration ---

    /**
     * Get Device Status
     */
    public function getDeviceStatus($deviceId)
    {
        try {
            return Http::withBasicAuth('ffa', 'qqffa')
                ->withHeaders(['X-Device-Id' => $deviceId])
                ->get('https://gowa.qlabcode.com/app/status');
        } catch (\Exception $e) {
            Log::error("[WhatsappService] getDeviceStatus Exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create Device
     */
    public function createDevice($deviceId)
    {
        try {
            return Http::withBasicAuth('ffa', 'qqffa')
                ->post('https://gowa.qlabcode.com/devices', [
                    'device_id' => $deviceId
                ]);
        } catch (\Exception $e) {
            Log::error("[WhatsappService] createDevice Exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Login / Get QR Code
     */
    public function loginDevice($deviceId)
    {
        try {
            return Http::withBasicAuth('ffa', 'qqffa')
                ->get("https://gowa.qlabcode.com/devices/{$deviceId}/login");
        } catch (\Exception $e) {
            Log::error("[WhatsappService] loginDevice Exception: " . $e->getMessage());
            return null;
        }
    }
}
