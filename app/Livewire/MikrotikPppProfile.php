<?php

namespace App\Livewire;

use App\Services\MikrotikService;
use Livewire\Component;

class MikrotikPppProfile extends Component
{
    public array $profiles = [];
    public bool $loading = true;
    public string $error = '';

    // Modal
    public bool $showModal = false;
    public bool $isEditing = false;
    public string $editId = '';

    // Form
    public string $name = '';
    public string $mikrotik_profile = '';
    public string $price = '';

    public array $mikrotik_profiles_list = [];

    public function mount(): void
    {
        $this->loadProfiles();
    }

    private function getMikrotik(): MikrotikService
    {
        $router = auth()->user()->router;
        if (!$router) {
            throw new \Exception('Router MikroTik belum dikonfigurasi.');
        }
        return new MikrotikService($router);
    }

    public function loadProfiles(): void
    {
        try {
            $this->loading = true;
            $this->error = '';
            $mikrotik = $this->getMikrotik();
            $data = $mikrotik->getPppProfiles();
            
            $this->mikrotik_profiles_list = collect($data)
                ->filter(fn($p) => !in_array(strtolower($p['name'] ?? ''), ['default', 'default-encryption']))
                ->pluck('name')
                ->toArray();

            $this->profiles = \App\Models\Package::where('user_id', auth()->id())
                              ->where('tipe', 'PPPOE')
                              ->get()->toArray();

        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function openCreate(): void
    {
        $this->reset(['editId', 'name', 'mikrotik_profile', 'price']);
        if (empty($this->mikrotik_profiles_list)) $this->loadProfiles();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit(string $id): void
    {
        if (empty($this->profiles)) $this->loadProfiles();
        $p = collect($this->profiles)->firstWhere('id', $id);
        if ($p) {
            $this->editId = $id;
            $this->name   = $p['name'] ?? '';
            $this->mikrotik_profile = $p['mikrotik_profile'] ?? '';
            $this->price  = rtrim(rtrim((string)($p['price'] ?? '0'), '0'), '.') ?: '0';
            if ($this->price == '0') $this->price = '';
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
            'name'             => 'required|string|max:100',
            'mikrotik_profile' => 'required|string',
            'price'            => 'nullable|numeric|min:0',
        ]);

        try {
            // Automatically grab the speed limits from MikroTik
            $mikrotik = $this->getMikrotik();
            $mikrotikProfiles = collect($mikrotik->getPppProfiles());
            $selectedProfile = $mikrotikProfiles->firstWhere('name', $this->mikrotik_profile);

            $upload = null;
            $download = null;

            if ($selectedProfile && !empty($selectedProfile['rate-limit'])) {
                $rl = $selectedProfile['rate-limit'];
                if (str_contains($rl, '/')) {
                    [$upload, $download] = explode('/', $rl, 2);
                }
            }

            if ($this->isEditing) {
                \App\Models\Package::where('id', $this->editId)
                    ->where('user_id', auth()->id())
                    ->update([
                        'name' => $this->name,
                        'mikrotik_profile' => $this->mikrotik_profile,
                        'price' => $this->price ?: 0,
                        'speed_upload' => $upload,
                        'speed_download' => $download,
                    ]);

                session()->flash('message', "Paket '{$this->name}' berhasil diperbarui.");
            } else {
                \App\Models\Package::create([
                    'user_id' => auth()->id(),
                    'tipe' => 'PPPOE',
                    'name' => $this->name,
                    'mikrotik_profile' => $this->mikrotik_profile,
                    'price' => $this->price ?: 0,
                    'speed_upload' => $upload,
                    'speed_download' => $download,
                ]);

                session()->flash('message', "Paket '{$this->name}' berhasil ditambahkan.");
            }
            $this->closeModal();
            $this->loadProfiles();
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function delete(string $id): void
    {
        $package = \App\Models\Package::where('user_id', auth()->id())
                     ->where('tipe', 'PPPOE')
                     ->where('id', $id)
                     ->first();
        if ($package) {
            try {
                $package->delete();
                $this->loadProfiles();
                session()->flash('message', 'Paket berhasil dihapus.');
            } catch (\Exception $e) {
                session()->flash('error', 'Gagal menghapus: ' . $e->getMessage());
            }
        }
    }

    public function render()
    {
        return view('livewire.mikrotik-ppp-profile')
            ->layout('layouts.app', ['header' => 'PPP Profile']);
    }
}
