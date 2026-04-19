<?php

namespace App\Livewire;

use App\Services\MikrotikService;
use Livewire\Component;

class MikrotikHotspotProfile extends Component
{
    public array $profiles = [];
    public array $ip_pools = [];
    public bool $loading = true;
    public string $error = '';

    // Modal
    public bool $showModal = false;
    public bool $isEditing = false;
    public string $editId = '';

    // Form
    public string $sync_mode = 'new'; // 'new' or 'sync'
    public string $name = '';
    public string $price = '';
    public string $download_value = '10', $download_unit = 'M';
    public string $upload_value = '10', $upload_unit = 'M';
    public string $shared_users = '1';
    public string $address_pool = 'none';
    public string $session_timeout = '8h';
    public string $selected_mikrotik_profile = '';

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
            
            // Get IP Pools
            $this->ip_pools = $mikrotik->getIpPools();

            // Get existing Mikrotik Profiles
            $allM = $mikrotik->getHotspotProfiles();
            $this->mikrotik_profiles_list = collect($allM)
                ->filter(fn($p) => strtolower($p['name'] ?? '') !== 'default')
                ->toArray();

            // Get packages from DB
            $this->profiles = \App\Models\Package::where('user_id', auth()->id())
                              ->where('tipe', 'hotspot')
                              ->get()->toArray();

        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function openCreate(): void
    {
        $this->reset(['editId', 'name', 'price', 'shared_users', 'address_pool', 'session_timeout', 'download_value', 'download_unit', 'upload_value', 'upload_unit', 'selected_mikrotik_profile', 'sync_mode']);
        $this->sync_mode = 'new';
        $this->session_timeout = '8h';
        $this->isEditing = false;
        $this->showModal = true;
        
        $this->loadProfiles();
    }

    public function openEdit(string $id): void
    {
        $p = \App\Models\Package::find($id);
        if ($p) {
            $this->editId = $id;
            $this->name   = $p->name;
            $this->price  = (string)$p->price;
            $this->sync_mode = 'new';
            
            // Fetch current settings from Mikrotik since they aren't in our DB
            try {
                $mikrotik = $this->getMikrotik();
                $allM = $mikrotik->getHotspotProfiles();
                $mProfile = collect($allM)->firstWhere('name', $p->mikrotik_profile);
                if ($mProfile) {
                    $this->shared_users = $mProfile['shared-users'] ?? '1';
                    $this->address_pool = $mProfile['address-pool'] ?? 'none';
                    $this->session_timeout = $mProfile['session-timeout'] ?? '8h';
                }
            } catch (\Exception $e) {}
            
            // Split Speed
            if (preg_match('/^(\d+)(K|M)$/i', $p->speed_download, $m)) {
                $this->download_value = $m[1];
                $this->download_unit = strtoupper($m[2]);
            }
            if (preg_match('/^(\d+)(K|M)$/i', $p->speed_upload, $m)) {
                $this->upload_value = $m[1];
                $this->upload_unit = strtoupper($m[2]);
            }

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
        // Clean price
        if (isset($this->price) && !is_numeric($this->price)) {
            $this->price = str_replace('.', '', $this->price);
        }

        if ($this->sync_mode === 'sync') {
            $this->validate([
                'selected_mikrotik_profile' => 'required',
                'name' => 'required|string|max:100',
                'price' => 'required|numeric|min:0',
            ]);
        } else {
            $this->validate([
                'name'           => 'required|string|max:100',
                'price'          => 'required|numeric|min:0',
                'download_value' => 'required|numeric|min:1',
                'upload_value'   => 'required|numeric|min:1',
                'shared_users'   => 'required|numeric|min:1',
                'session_timeout' => 'required|string',
            ]);
        }

        try {
            $mikrotik = $this->getMikrotik();
            
            $profileName = $this->sync_mode === 'sync' ? $this->selected_mikrotik_profile : $this->name;
            $upload = $this->upload_value . $this->upload_unit;
            $download = $this->download_value . $this->download_unit;
            $shusers = $this->shared_users;
            $pool = $this->address_pool;
            $stimeout = $this->session_timeout;

            if ($this->sync_mode === 'sync') {
                $mProfile = collect($this->mikrotik_profiles_list)->firstWhere('name', $this->selected_mikrotik_profile);
                if ($mProfile) {
                    if (!empty($mProfile['rate-limit']) && str_contains($mProfile['rate-limit'], '/')) {
                        [$upload, $download] = explode('/', $mProfile['rate-limit'], 2);
                    }
                    $shusers = $mProfile['shared-users'] ?? '1';
                    $pool = $mProfile['address-pool'] ?? 'none';
                    $stimeout = $mProfile['session-timeout'] ?? '8h';
                }
            }

            $rateLimit = $upload . '/' . $download;

            if ($this->isEditing) {
                $package = \App\Models\Package::find($this->editId);
                
                if ($this->sync_mode === 'new') {
                    $allM = $mikrotik->getHotspotProfiles();
                    $mRecord = collect($allM)->firstWhere('name', $package->mikrotik_profile);
                    if ($mRecord) {
                        $mikrotik->updateHotspotProfileFull($mRecord['.id'], $this->name, $rateLimit, $shusers, $pool, $stimeout);
                    } else {
                        $mikrotik->addHotspotProfileFull($this->name, $rateLimit, $shusers, $pool, $stimeout);
                    }
                }

                $package->update([
                    'name' => $this->name,
                    'mikrotik_profile' => $profileName,
                    'price' => $this->price ?: 0,
                    'speed_upload' => $upload,
                    'speed_download' => $download,
                ]);

                session()->flash('message', "Profil Hotspot berhasil diperbarui.");
            } else {
                if ($this->sync_mode === 'new') {
                    $mikrotik->addHotspotProfileFull($this->name, $rateLimit, $shusers, $pool, $stimeout);
                }

                \App\Models\Package::create([
                    'user_id' => auth()->id(),
                    'tipe' => 'hotspot',
                    'name' => $this->name,
                    'mikrotik_profile' => $profileName,
                    'price' => $this->price ?: 0,
                    'speed_upload' => $upload,
                    'speed_download' => $download,
                ]);

                session()->flash('message', "Profil Hotspot berhasil ditambahkan.");
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
                     ->where('tipe', 'hotspot')
                     ->where('id', $id)
                     ->first();
        if ($package) {
            try {
                // Try delete from Mikrotik
                try {
                    $mikrotik = $this->getMikrotik();
                    $allM = $mikrotik->getHotspotProfiles();
                    $mRecord = collect($allM)->firstWhere('name', $package->mikrotik_profile);
                    if ($mRecord) {
                        $mikrotik->removeHotspotProfile($mRecord['.id']);
                    }
                } catch (\Exception $e) {}

                $package->delete();
                $this->loadProfiles();
                session()->flash('message', 'Profil berhasil dihapus dari sistem dan MikroTik.');
            } catch (\Exception $e) {
                session()->flash('error', 'Gagal menghapus: ' . $e->getMessage());
            }
        }
    }

    public function render()
    {
        return view('livewire.mikrotik-hotspot-profile')
            ->layout('layouts.app', ['header' => 'Hotspot User Profile']);
    }
}
