<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\WhatsappService;

class WhatsappManager extends Component
{
    public $deviceId;
    public $serverName;
    public $isConnected = false;
    public $qrCode = null;
    public $statusMessage = 'Memeriksa status server...';
    public $showConnectButton = false;

    public function mount()
    {
        $user = auth()->user()->load('whatsappServer');
        $this->deviceId = $user->phone;
        $this->serverName = $user->whatsappServer->name ?? 'Server Utama (Default)';
        
        $this->checkStatus();
    }

    public function checkStatus()
    {
        if (!$this->deviceId) {
            $this->statusMessage = 'Device ID (Nomor HP) tidak ditemukan.';
            return;
        }

        $service = new WhatsappService();
        $response = $service->getDeviceStatus($this->deviceId);

        if ($response) {
            $data = $response->json();
            
            if ($response->status() == 404 || ($data['code'] ?? '') === 'DEVICE_NOT_FOUND') {
                $this->statusMessage = 'Device belum terdaftar. Menambahkan device...';
                $this->autoCreateDevice();
            } elseif ($response->successful()) {
                $isConnected = ($data['is_connected'] ?? false) || 
                               (isset($data['results']['status']) && in_array($data['results']['status'], ['AUTHENTICATED', 'CONNECTED']));
                               
                if ($isConnected) {
                    $this->isConnected = true;
                    $this->qrCode = null;
                    $this->showConnectButton = false;
                    $this->statusMessage = 'Koneksi Berhasil! WhatsApp sudah terkoneksi.';
                } else {
                    $this->isConnected = false;
                    $this->qrCode = null;
                    $this->showConnectButton = true;
                    $this->statusMessage = 'WhatsApp belum terkoneksi.';
                }
            } else {
                $this->statusMessage = 'Gagal mengambil status: ' . ($data['message'] ?? 'Unknown error');
            }
        } else {
            $this->statusMessage = 'Gagal menghubungi server WhatsApp.';
        }
    }

    public function autoCreateDevice()
    {
        $service = new WhatsappService();
        $response = $service->createDevice($this->deviceId);
        
        if ($response && $response->successful()) {
            // Berhasil menambah device, lanjut minta QR Code
            $this->generateQr();
        } else {
            $this->statusMessage = 'Gagal mendaftarkan device ke server WhatsApp.';
        }
    }

    public function generateQr()
    {
        $this->statusMessage = 'Meminta QR Code...';
        
        $service = new WhatsappService();
        $response = $service->loginDevice($this->deviceId);

        if ($response && $response->successful()) {
            $data = $response->json();
            if (isset($data['results']['qr_link'])) {
                $this->qrCode = $data['results']['qr_link'];
                $this->showConnectButton = false;
                $this->statusMessage = 'Siap di Scan. Waktu scan ' . ($data['results']['qr_duration'] ?? 30) . ' detik.';
            } else {
                $this->statusMessage = 'QR Link tidak ditemukan dalam respons.';
            }
        } else {
            $this->statusMessage = 'Gagal generate QR Code.';
        }
    }

    public function reconnect()
    {
        $this->qrCode = null;
        $this->showConnectButton = false;
        $this->checkStatus();
    }

    public function render()
    {
        return view('livewire.whatsapp-manager')
            ->layout('layouts.app');
    }
}
