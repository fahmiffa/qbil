<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct(?string $baseUrl = null, ?string $apiKey = null)
    {
        $this->baseUrl = rtrim($baseUrl ?? config('services.whatsapp.url', env('WHATSAPP_API_URL', 'https://wagate.qlabcode.com')), '/');
        $this->apiKey = $apiKey ?? config('services.whatsapp.api_key', env('WHATSAPP_API_KEY', 'wag_mRqnmGTR4uVOAqbPSheP3kLhxlJelNVn'));
    }

    /**
     * Get WhatsApp connection status.
     *
     * @return array
     */
    public function getStatus(): array
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
                'Accept' => 'application/json',
            ])->timeout(15)->get("{$this->baseUrl}/api/whatsapp/status");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Whatsapp getStatus API failed (' . $response->status() . '): ' . $response->body());
            return [
                'success' => false,
                'status' => $response->status(),
                'data' => [
                    'code' => 'ERROR',
                    'message' => 'Gagal mengambil status koneksi WhatsApp (' . $response->status() . ').',
                    'results' => [
                        'device_id' => '',
                        'is_connected' => false,
                        'is_logged_in' => false,
                        'jid' => '',
                    ],
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Whatsapp getStatus Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'status' => 500,
                'data' => [
                    'code' => 'EXCEPTION',
                    'message' => $e->getMessage(),
                    'results' => [
                        'device_id' => '',
                        'is_connected' => false,
                        'is_logged_in' => false,
                        'jid' => '',
                    ],
                ],
            ];
        }
    }

    /**
     * Request QR Code for login.
     *
     * @return array
     */
    public function getLoginQr(): array
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
                'Accept' => 'application/json',
            ])->timeout(20)->get("{$this->baseUrl}/api/whatsapp/login");

            if ($response->successful()) {
                $data = $response->json();
                
                // Adjust qr_link protocol if needed
                if (!empty($data['data']['results']['qr_link']) && request()->isSecure()) {
                    $data['data']['results']['qr_link'] = str_replace('http://', 'https://', $data['data']['results']['qr_link']);
                }

                return $data;
            }

            Log::error('Whatsapp getLoginQr API failed (' . $response->status() . '): ' . $response->body());
            return [
                'success' => false,
                'status' => $response->status(),
                'data' => [
                    'code' => 'ERROR',
                    'message' => 'Gagal meminta QR login: ' . $response->status(),
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Whatsapp getLoginQr Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'status' => 500,
                'data' => [
                    'code' => 'EXCEPTION',
                    'message' => $e->getMessage(),
                ],
            ];
        }
    }

    /**
     * Send WhatsApp message.
     * Compatible with both:
     * - sendMessage(string $senderPhone, string $receiverPhone, string $message): bool
     * - sendMessage(string $receiverPhone, string $message): bool
     *
     * @param string|null $senderOrReceiver
     * @param string $receiverOrMessage
     * @param string|null $message
     * @return bool
     */
    public function sendMessage(?string $senderOrReceiver, string $receiverOrMessage, ?string $message = null): bool
    {
        if ($message === null) {
            $receiverPhone = (string) $senderOrReceiver;
            $messageText = $receiverOrMessage;
        } else {
            $receiverPhone = $receiverOrMessage;
            $messageText = $message;
        }

        // Clean receiver phone
        $receiverPhone = trim($receiverPhone);

        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->timeout(20)->post("{$this->baseUrl}/api/whatsapp/send", [
                'phone' => $receiverPhone,
                'message' => $messageText,
            ]);

            if ($response->successful()) {
                $body = $response->json();
                if (($body['success'] ?? false) === true || ($body['status'] ?? 0) === 200) {
                    return true;
                }
            }

            Log::error('Whatsapp Send API failed (' . $response->status() . '): ' . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error('Whatsapp Service Send Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Replace placeholders in template.
     *
     * @param string $template
     * @param array $data
     * @return string
     */
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
}
