<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    protected string $baseUrl = 'https://waone.qlabcode.com/api/send';

    /**
     * Send WhatsApp message.
     *
     * @param string $senderPhone The sender (user) phone number
     * @param string $receiverPhone The receiver (customer) phone number
     * @param string $message The message content
     * @return bool
     */
    public function sendMessage(string $senderPhone, string $receiverPhone, string $message): bool
    {
        // Format receiver phone (replace leading 0 with 62)
        if (str_starts_with($receiverPhone, '0')) {
            $receiverPhone = '62' . substr($receiverPhone, 1);
        }

        try {
            $response = Http::post($this->baseUrl, [
                'number' => $senderPhone,
                'message' => $message,
                'to' => $receiverPhone,
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('Whatsapp API failed: ' . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error('Whatsapp Service Exception: ' . $e->getMessage());
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
