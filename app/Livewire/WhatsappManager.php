<?php

namespace App\Livewire;

use App\Services\WhatsappService;
use Livewire\Component;

class WhatsappManager extends Component
{
    public ?string $deviceId = null;
    public ?string $qr = null;
    public bool $isConnected = false;
    public bool $isLoggedIn = false;
    public ?string $jid = null;
    public string $status = 'Menghubungkan ke WhatsApp Service...';
    public ?string $lastRefreshedAt = null;
    public bool $isLoading = false;

    public function mount(WhatsappService $waService)
    {
        $this->deviceId = auth()->user()?->phone;
        $this->refreshStatusAndQr($waService);
    }

    /**
     * Poll status and QR code every 10 seconds.
     */
    public function refreshStatusAndQr(WhatsappService $waService)
    {
        $this->isLoading = true;

        try {
            $statusResponse = $waService->getStatus();
            $statusResults = $statusResponse['data']['results'] ?? [];

            $isConnected = !empty($statusResults['is_connected']);
            $isLoggedIn = !empty($statusResults['is_logged_in']);
            $deviceId = !empty($statusResults['device_id']) ? $statusResults['device_id'] : ($this->deviceId ?? auth()->user()?->phone);

            $this->deviceId = $deviceId;
            $this->isConnected = $isConnected;
            $this->isLoggedIn = $isLoggedIn;
            $this->jid = $statusResults['jid'] ?? null;

            if ($isConnected && $isLoggedIn) {
                $this->qr = null;
                $this->status = 'WhatsApp Terhubung!';
            } else {
                // Device not logged in / connected, fetch new QR code
                $qrResponse = $waService->getLoginQr();
                $qrResults = $qrResponse['data']['results'] ?? [];

                if (!empty($qrResults['qr_link'])) {
                    $this->qr = $qrResults['qr_link'];
                    if (!empty($qrResults['device_id'])) {
                        $this->deviceId = $qrResults['device_id'];
                    }
                    $this->status = 'Siap di Scan. QR code otomatis diperbarui setiap 10 detik.';
                } else {
                    $this->status = $qrResponse['data']['message'] ?? 'Menunggu QR Code dari server...';
                }
            }

            $this->lastRefreshedAt = now()->format('H:i:s');
        } catch (\Exception $e) {
            $this->status = 'Terjadi kesalahan: ' . $e->getMessage();
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.whatsapp-manager')
            ->layout('layouts.app');
    }
}
