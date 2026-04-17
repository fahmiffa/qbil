<?php

namespace App\Livewire;

use App\Models\Router;
use Livewire\Component;
use RouterOS\Client;
use RouterOS\Query;

class RouterConfig extends Component
{
    public $name, $host, $port = 8728, $username, $password;
    public $status_connection = 'Disconnected';
    public $message = '';

    public function mount()
    {
        $router = auth()->user()->router;
        if ($router) {
            $this->name = $router->name;
            $this->host = $router->host;
            $this->port = $router->port;
            $this->username = $router->username;
            $this->password = $router->password;
        }
    }

    public function render()
    {
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
            $client = new Client([
                'host' => $this->host,
                'user' => $this->username,
                'pass' => $this->password,
                'port' => (int) $this->port,
                'timeout' => 5,
            ]);

            $this->status_connection = 'Connected';
            $this->message = 'Koneksi ke MikroTik berhasil!';
            $this->dispatch('toast', type: 'success', message: $this->message);
        } catch (\Exception $e) {
            $this->status_connection = 'Error';
            $this->message = 'Koneksi gagal: ' . $e->getMessage();
            $this->dispatch('toast', type: 'error', message: $this->message);
        }
    }
}
