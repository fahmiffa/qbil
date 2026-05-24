<?php

namespace App\Livewire;

use App\Models\Router;
use App\Models\ActivityLog;
use App\Services\MikrotikService;
use Livewire\Component;

class RouterConfig extends Component
{
    // Form fields
    public $router_id_edit = null;
    public $name, $host, $port = 8728, $username, $password, $is_active = true;
    public $showPassword = false;
    public $isOpen = false;

    // Per-router test state
    public $testingRouterId = null;

    public function render()
    {
        $routers = Router::where('user_id', auth()->id())
            ->orderBy('id')
            ->get();

        return view('livewire.router-config', ['routers' => $routers])
            ->layout('layouts.app');
    }

    // -------------------------
    // Modal
    // -------------------------

    public function create()
    {
        $user = auth()->user();
        if (!$user->allow_multi_router && $user->role != 0 && $user->routers()->count() >= 1) {
            $this->dispatch('toast', type: 'error', message: 'Limit tercapai. Anda hanya diizinkan memiliki 1 router.');
            return;
        }
        $this->resetForm();
        $this->isOpen = true;
        $this->resetValidation();
    }

    public function edit($id)
    {
        $router = Router::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $this->router_id_edit = $router->id;
        $this->name           = $router->name;
        $this->host           = $router->host;
        $this->port           = $router->port;
        $this->username       = $router->username;
        $this->password       = $router->password;
        $this->is_active      = $router->is_active;
        $this->showPassword   = false;
        $this->isOpen         = true;
        $this->resetValidation();
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->router_id_edit = null;
        $this->name           = '';
        $this->host           = '';
        $this->port           = 8728;
        $this->username       = '';
        $this->password       = '';
        $this->is_active      = true;
        $this->showPassword   = false;
    }

    // -------------------------
    // CRUD
    // -------------------------

    public function save()
    {
        $this->validate([
            'name'     => 'nullable|string|max:100',
            'host'     => 'required|string|max:255',
            'port'     => 'required|numeric|min:1|max:65535',
            'username' => 'required|string|max:100',
            'password' => 'required|string|max:255',
        ]);

        try {
            if ($this->router_id_edit) {
                // Update existing router
                $router = Router::where('id', $this->router_id_edit)
                    ->where('user_id', auth()->id())
                    ->firstOrFail();

                $router->update([
                    'name'     => $this->name,
                    'host'     => $this->host,
                    'port'     => $this->port,
                    'username' => $this->username,
                    'password' => $this->password,
                    'is_active' => $this->is_active,
                ]);

                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'title'   => 'UPDATE ROUTER',
                    'message' => "Memperbarui router: {$this->name} ({$this->host})",
                    'type'    => 'router_crud',
                ]);

                $this->dispatch('toast', type: 'success', message: 'Router berhasil diperbarui.');
            } else {
                // Check limit just in case
                $user = auth()->user();
                if (!$user->allow_multi_router && $user->role != 0 && $user->routers()->count() >= 1) {
                    $this->dispatch('toast', type: 'error', message: 'Limit tercapai. Anda hanya diizinkan memiliki 1 router.');
                    return;
                }

                // Create new router
                Router::create([
                    'user_id'  => auth()->id(),
                    'name'     => $this->name ?: $this->host,
                    'host'     => $this->host,
                    'port'     => $this->port,
                    'username' => $this->username,
                    'password' => $this->password,
                    'is_active' => $this->is_active,
                ]);

                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'title'   => 'TAMBAH ROUTER',
                    'message' => "Menambahkan router: {$this->name} ({$this->host})",
                    'type'    => 'router_crud',
                ]);

                $this->dispatch('toast', type: 'success', message: 'Router baru berhasil ditambahkan.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        try {
            $router = Router::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
            $router->is_active = !$router->is_active;
            $router->save();

            $statusText = $router->is_active ? 'diaktifkan' : 'dinonaktifkan';
            $this->dispatch('toast', type: 'success', message: "Router {$router->name} berhasil {$statusText}.");

            ActivityLog::create([
                'user_id' => auth()->id(),
                'title'   => 'TOGGLE STATUS ROUTER',
                'message' => "Mengubah status router {$router->name} menjadi " . ($router->is_active ? 'Active' : 'Disabled'),
                'type'    => 'router_crud',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Gagal mengubah status: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $router = Router::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

            // Cek apakah masih ada pelanggan atau paket yang menggunakan router ini
            $customerCount = $router->customers()->count();
            $packageCount  = $router->packages()->count();

            if ($customerCount > 0 || $packageCount > 0) {
                $this->dispatch('toast', type: 'error', message: "Router tidak bisa dihapus. Masih digunakan oleh {$customerCount} pelanggan dan {$packageCount} paket.");
                return;
            }

            $routerName = $router->name ?: $router->host;
            $router->delete();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'title'   => 'HAPUS ROUTER',
                'message' => "Menghapus router: {$routerName}",
                'type'    => 'router_crud',
            ]);

            $this->dispatch('toast', type: 'success', message: 'Router berhasil dihapus.');
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    // -------------------------
    // Test Connection
    // -------------------------

    public function testConnection($id)
    {
        $this->testingRouterId = $id;

        try {
            $router = Router::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
            \App\Jobs\CheckRouterConnectionJob::dispatchSync($router);
            $router->refresh();

            if ($router->connection_status === 'online') {
                $this->dispatch('toast', type: 'success', message: "✅ Router {$router->name}: Koneksi berhasil! Ping {$router->ping_ms}ms");
            } else {
                $this->dispatch('toast', type: 'error', message: "❌ Router {$router->name}: Koneksi gagal. " . ($router->connection_error ?? ''));
            }
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Test gagal: ' . $e->getMessage());
        }

        $this->testingRouterId = null;
    }

    public function togglePassword()
    {
        $this->showPassword = !$this->showPassword;
    }
}
