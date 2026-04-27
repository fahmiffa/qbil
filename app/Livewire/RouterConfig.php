<?php

namespace App\Livewire;

use App\Models\Router;
use App\Services\MikrotikService;
use Livewire\Component;
use RouterOS\Client;
use RouterOS\Query;

class RouterConfig extends Component
{
    public $name, $host, $port = 8728, $username, $password;
    public $status_connection = 'Disconnected';
    public $ping_ms, $last_checked_at, $connection_error;
    public $message = '';
    public $showPassword = false;

    public function mount()
    {
        $router = auth()->user()->router;
        if ($router) {
            $this->name = $router->name;
            $this->host = $router->host;
            $this->port = $router->port;
            $this->username = $router->username;
            $this->password = $router->password;
            $this->status_connection = $router->connection_status;
            $this->ping_ms = $router->ping_ms;
            $this->last_checked_at = $router->last_checked_at ? $router->last_checked_at->format('Y-m-d H:i:s') : null;
            $this->connection_error = $router->connection_error;
        }
    }

    public function render()
    {
        // Selalu ambil data terbaru dari DB untuk polling status
        $router = auth()->user()->router;
        if ($router) {
            $this->status_connection = $router->connection_status;
            $this->ping_ms = $router->ping_ms;
            $this->last_checked_at = $router->last_checked_at ? $router->last_checked_at->format('Y-m-d H:i:s') : null;
            $this->connection_error = $router->connection_error;
        }

        return view('livewire.router-config')
            ->layout('layouts.app');
    }

    public function save()
    {
        $this->validate([
            'host' => 'required',
            'port' => 'required|numeric',
            'username' => 'required',
            'password' => 'required',
        ]);

        try {
            Router::updateOrCreate(
                ['user_id' => auth()->id()],
                [
                    'name' => $this->name,
                    'host' => $this->host,
                    'port' => $this->port,
                    'username' => $this->username,
                    'password' => $this->password,
                ]
            );

            $this->dispatch('toast', type: 'success', message: 'Konfigurasi router berhasil disimpan.');
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Gagal menyimpan konfigurasi: ' . $e->getMessage());
        }
    }

    public function testConnection()
    {
        $this->validate([
            'host' => 'required',
            'port' => 'required|numeric',
            'username' => 'required',
            'password' => 'required',
        ]);

        try {
            $router = auth()->user()->router;
            if (!$router) {
                // If not saved yet, create a temporary router object for the service
                $router = new \App\Models\Router([
                    'host' => $this->host,
                    'username' => $this->username,
                    'password' => $this->password,
                    'port' => $this->port,
                ]);
            }
            $service = MikrotikService::getInstance($router);
            if ($service->checkConnection()) {
                $this->status_connection = 'Connected';
                $this->message = 'Koneksi ke MikroTik berhasil!';
                $this->dispatch('toast', type: 'success', message: $this->message);
            } else {
                throw new \Exception("Gagal melakukan handshake dengan MikroTik.");
            }
        } catch (\Exception $e) {
            $this->status_connection = 'Error';
            $this->message = 'Koneksi gagal: ' . $e->getMessage();
            $this->dispatch('toast', type: 'error', message: $this->message);
        }
    }

    public function togglePassword()
    {
        $this->showPassword = !$this->showPassword;
    }
}
