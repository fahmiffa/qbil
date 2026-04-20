<?php

namespace App\Livewire;

use App\Services\MikrotikService;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class MikrotikIpPool extends Component
{
    public array $pools = [];
    public bool $loading = true;
    public string $error = '';

    // Modal state
    public bool $showModal = false;
    public bool $isEditing = false;
    public string $editId = '';

    // Form fields
    public string $name = '';
    public string $ranges = '';
    public string $next_pool = 'none';

    public function mount(): void
    {
        $this->loadPools();
    }

    private function getMikrotik(): MikrotikService
    {
        $router = auth()->user()->router;
        if (!$router) {
            throw new \Exception('Router MikroTik belum dikonfigurasi.');
        }
        return new MikrotikService($router);
    }

    public function loadPools(): void
    {
        try {
            $this->loading = true;
            $this->error = '';
            $mikrotik = $this->getMikrotik();
            $routerId = auth()->user()->router->id ?? 0;

            $pools = Cache::remember("mk_pools_{$routerId}", 300, function() use ($mikrotik) {
                return $mikrotik->getIpPools();
            });

            $this->pools = collect($pools)->map(function($pool) {
                $pool['id'] = $pool['.id'] ?? null;
                return $pool;
            })->toArray();
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function openCreate(): void
    {
        $this->reset(['editId', 'name', 'ranges', 'next_pool']);
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit(string $id): void
    {
        if (empty($this->pools)) {
            $this->loadPools();
        }

        $pool = collect($this->pools)->firstWhere('id', $id);
        if ($pool) {
            $this->editId    = $id;
            $this->name      = $pool['name'] ?? '';
            $this->ranges    = $pool['ranges'] ?? '';
            $this->next_pool = $pool['next-pool'] ?? 'none';
            if ($this->next_pool === '') $this->next_pool = 'none';
            $this->isEditing = true;
            $this->showModal = true;
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate([
            'name'   => 'required|string|max:50',
            'ranges' => 'required|string',
        ]);

        try {
            $mikrotik = $this->getMikrotik();

            if ($this->isEditing) {
                $mikrotik->updateIpPool($this->editId, $this->name, $this->ranges, $this->next_pool);
                session()->flash('message', "IP Pool '{$this->name}' berhasil diperbarui.");
            } else {
                $mikrotik->addIpPool($this->name, $this->ranges, $this->next_pool);
                session()->flash('message', "IP Pool '{$this->name}' berhasil ditambahkan ke MikroTik.");
            }

            $this->closeModal();
            $routerId = auth()->user()->router->id ?? 0;
            Cache::forget("mk_pools_{$routerId}");
            $this->loadPools();
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function delete(string $id): void
    {
        try {
            $mikrotik = $this->getMikrotik();
            $mikrotik->removeIpPool($id);
            
            $routerId = auth()->user()->router->id ?? 0;
            Cache::forget("mk_pools_{$routerId}");
            
            $this->loadPools();
            session()->flash('message', 'IP Pool berhasil dihapus.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.mikrotik-ip-pool')
            ->layout('layouts.app', ['header' => 'IP Pool MikroTik']);
    }
}
