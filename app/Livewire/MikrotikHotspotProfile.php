<?php

namespace App\Livewire;

use App\Services\MikrotikService;
use Illuminate\Support\Facades\Cache;
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
    public string $address_pool = 'none';
    public string $limit_time = '';
    public string $masa_aktif = '';       // Masa aktif setelah login pertama, contoh: "1d"
    public string $valid_duration = '';   // Masa berlaku sebelum aktivasi, contoh: "30d"
    public string $selected_mikrotik_profile = '';

    public array $mikrotik_profiles_list = [];

    public function mount(): void
    {
        // Data will be loaded via wire:init to prevent blocking the initial page load
    }

    private function getMikrotik(): MikrotikService
    {
        $router = auth()->user()->router;
        if (!$router) {
            throw new \Exception('Router MikroTik belum dikonfigurasi.');
        }
        return MikrotikService::getInstance($router);
    }

    public function loadProfiles(): void
    {
        try {
            $this->loading = true;
            $this->error = '';
            $mikrotik = $this->getMikrotik();
            $routerId = auth()->user()->router->id ?? 0;
            
            // Get IP Pools (Cached - 1 hour)
            $this->ip_pools = Cache::remember("mk_pools_{$routerId}", 3600, fn() => $mikrotik->getIpPools());

            // Get existing Mikrotik Profiles (Cached - 1 hour)
            $allM = Cache::remember("mk_hs_profiles_{$routerId}", 3600, fn() => $mikrotik->getHotspotProfiles());
            
            $this->mikrotik_profiles_list = collect($allM)
                ->filter(fn($p) => strtolower($p['name'] ?? '') !== 'default')
                ->toArray();

            // Get packages from DB
            $this->profiles = \App\Models\Package::where('user_id', auth()->id())
                              ->where('tipe', 'HOTSPOT')
                              ->get()->toArray();

        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function openCreate(): void
    {
        $this->reset(['editId', 'name', 'price', 'address_pool', 'limit_time', 'masa_aktif', 'valid_duration', 'download_value', 'download_unit', 'upload_value', 'upload_unit', 'selected_mikrotik_profile', 'sync_mode']);
        $this->sync_mode = 'new';
        $this->limit_time = '';
        $this->masa_aktif = '';
        $this->valid_duration = '';
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
            $this->price  = (string)(int)$p->price;
            $this->limit_time    = $p->limit_time ?? '';
            $this->masa_aktif    = $p->masa_aktif ?? '';
            $this->valid_duration = $p->valid_duration ?? '';
            $this->sync_mode = 'new';
            
            // Fetch current settings from Mikrotik since they aren't in our DB
            try {
                $mikrotik = $this->getMikrotik();
                $allM = $mikrotik->getHotspotProfiles();
                $mProfile = collect($allM)->firstWhere('name', $p->mikrotik_profile);
                if ($mProfile) {
                    $this->address_pool = $mProfile['address-pool'] ?? 'none';
                }
            } catch (\Exception $e) {
                // Ignore
            }
            
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
        if (isset($this->price)) {
            $this->price = str_replace('.', '', $this->price);
        }

        if ($this->sync_mode === 'sync') {
            $this->validate([
                'selected_mikrotik_profile' => 'required',
                'name'       => 'required|string|max:100',
                'price'      => 'required|numeric|min:0',
                'limit_time' => 'nullable|string',
                'masa_aktif'    => 'nullable|string',
                'valid_duration' => 'nullable|string',
            ]);
        } else {
            $this->validate([
                'name'           => 'required|string|max:100',
                'price'          => 'required|numeric|min:0',
                'download_value' => 'required|numeric|min:1',
                'upload_value'   => 'required|numeric|min:1',
                'limit_time'     => 'nullable|string',
                'masa_aktif'     => 'nullable|string',
                'valid_duration' => 'nullable|string',
            ]);
        }

        try {
            $mikrotik = $this->getMikrotik();
            
            $profileName = $this->sync_mode === 'sync' ? $this->selected_mikrotik_profile : $this->name;
            $upload = $this->upload_value . $this->upload_unit;
            $download = $this->download_value . $this->download_unit;
            $pool = $this->address_pool;

            if ($this->sync_mode === 'sync') {
                $mProfile = collect($this->mikrotik_profiles_list)->firstWhere('name', $this->selected_mikrotik_profile);
                if ($mProfile) {
                    if (!empty($mProfile['rate-limit'])) {
                        if (str_contains($mProfile['rate-limit'], '/')) {
                            $parts = explode('/', $mProfile['rate-limit'], 2);
                            $upload = $parts[0] ?? '10M';
                            $download = $parts[1] ?? '10M';
                        } else {
                            // Jika hanya ada satu nilai, asumsikan itu download, upload default
                            $download = $mProfile['rate-limit'];
                            $upload = '1M'; 
                        }
                    }
                    $pool = $mProfile['address-pool'] ?? 'none';
                }
            }

            $rateLimit = $upload . '/' . $download;

            if ($this->isEditing) {
                $package = \App\Models\Package::find($this->editId);
                
                $package->update([
                    'name'             => $this->name,
                    'mikrotik_profile' => $profileName,
                    'price'            => $this->price ?: 0,
                    'speed_upload'     => $upload,
                    'speed_download'   => $download,
                    'limit_time'       => $this->limit_time,
                    'masa_aktif'       => $this->masa_aktif,
                    'valid_duration'   => $this->valid_duration,
                ]);

                if ($this->sync_mode === 'new') {
                    // Sync ke Mikrotik via Job
                    \App\Jobs\ProvisionPackageJob::dispatchSync($package, 'update', $package->mikrotik_profile, [
                        'address_pool' => $pool,
                    ]);
                }

                session()->flash('message', "Profil Hotspot berhasil diperbarui.");
            } else {
                $package = \App\Models\Package::create([
                    'user_id'          => auth()->id(),
                    'tipe'             => 'HOTSPOT',
                    'name'             => $this->name,
                    'mikrotik_profile' => $profileName,
                    'price'            => $this->price ?: 0,
                    'speed_upload'     => $upload,
                    'speed_download'   => $download,
                    'limit_time'       => $this->limit_time,
                    'masa_aktif'       => $this->masa_aktif,
                    'valid_duration'   => $this->valid_duration,
                ]);

                if ($this->sync_mode === 'new') {
                     \App\Jobs\ProvisionPackageJob::dispatchSync($package, 'create', null, [
                        'address_pool' => $pool,
                    ]);
                }

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
                // Dispatch Job Sync untuk delete
                \App\Jobs\ProvisionPackageJob::dispatchSync($package, 'delete');

                $package->delete();
                $this->loadProfiles();
                session()->flash('message', 'Profil berhasil dihapus.');
            } catch (\Exception $e) {
                session()->flash('error', 'Gagal menghapus: ' . $e->getMessage());
            }
        }
    }

    public function render()
    {
        return view('livewire.mikrotik-hotspot-profile')
            ->layout('layouts.app', ['header' => 'Hotspot']);
    }
}
