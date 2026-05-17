<?php

namespace App\Livewire;

use App\Services\MikrotikService;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class MikrotikPppProfile extends Component
{
    public array $profiles = [];
    public array $ip_pools = [];
    public bool $loading = true;
    public string $error = '';
    public string $router_id = '';
    public $routers = [];

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
    public string $local_address = '';
    public string $remote_address = '';
    public string $selected_mikrotik_profile = '';

    public array $mikrotik_profiles_list = [];

    public function mount(): void
    {
        $this->routers = \App\Models\Router::where('user_id', auth()->id())->orderBy('id')->get();
        $this->router_id = auth()->user()->routers()->oldest()->value('id') ?? '';
        $this->loadProfiles();
    }

    public function updatedRouterId()
    {
        $this->loadProfiles();
    }

    private function getMikrotik(): MikrotikService
    {
        $router = \App\Models\Router::where('user_id', auth()->id())->where('id', $this->router_id)->first() 
                    ?? auth()->user()->routers()->oldest()->first();
                    
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
            
            // Get IP Pools from Mikrotik (Cached)
            $this->ip_pools = Cache::remember("mk_pools_{$routerId}", 300, fn() => $mikrotik->getIpPools());

            // Get PPP Profiles from Mikrotik (Cached)
            $allM = Cache::remember("mk_ppp_profiles_{$routerId}", 300, fn() => $mikrotik->getPppProfiles());
            
            $this->mikrotik_profiles_list = collect($allM)
                ->filter(fn($p) => !in_array(strtolower($p['name'] ?? ''), ['default', 'default-encryption']))
                ->toArray();

            // Get local Packages that are PPPOE and belong to this router
            $this->profiles = \App\Models\Package::where('user_id', auth()->id())
                              ->where('router_id', $this->router_id)
                              ->where('tipe', 'PPPOE')
                              ->get()->toArray();

        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function updatedRemoteAddress($value)
    {
        if (!$value) return;
        
        $pool = collect($this->ip_pools)->firstWhere('name', $value);
        if ($pool && !empty($pool['ranges'])) {
            $range = $pool['ranges']; // e.g. "192.168.10.2-192.168.10.254"
            if (preg_match('/(\d+\.\d+\.\d+)\.\d+/', $range, $matches)) {
                $this->local_address = $matches[1] . '.1';
            }
        }
    }

    public function openCreate(): void
    {
        $this->reset(['editId', 'name', 'price', 'local_address', 'remote_address', 'download_value', 'download_unit', 'upload_value', 'upload_unit', 'selected_mikrotik_profile', 'sync_mode']);
        $this->sync_mode = 'new';
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
            $this->sync_mode = 'new'; // Default to new for editing
            
            // Split Speed
            if (preg_match('/^(\d+)(K|M)$/i', $p->speed_download, $m)) {
                $this->download_value = $m[1];
                $this->download_unit = strtoupper($m[2]);
            }
            if (preg_match('/^(\d+)(K|M)$/i', $p->speed_upload, $m)) {
                $this->upload_value = $m[1];
                $this->upload_unit = strtoupper($m[2]);
            }

            // Get current profile details from Mikrotik
            try {
                $mikrotik = $this->getMikrotik();
                $allProfiles = $mikrotik->getPppProfiles();
                $mProfile = collect($allProfiles)->firstWhere('name', $p->mikrotik_profile);
                if ($mProfile) {
                    $this->local_address = $mProfile['local-address'] ?? '';
                    $this->remote_address = $mProfile['remote-address'] ?? '';
                }
            } catch (\Exception $e) {}

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
        // Bersihkan format titik harga
        if (isset($this->price)) {
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
                'local_address'  => 'required|string',
                'remote_address' => 'required|string',
            ]);
        }

        try {
            $mikrotik = $this->getMikrotik();
            
            $profileName = $this->sync_mode === 'sync' ? $this->selected_mikrotik_profile : $this->name;
            $upload = $this->upload_value . $this->upload_unit;
            $download = $this->download_value . $this->download_unit;

            if ($this->sync_mode === 'sync') {
                // Fetch details from existing profile on Mikrotik to get the speed
                $mProfile = collect($this->mikrotik_profiles_list)->firstWhere('name', $this->selected_mikrotik_profile);
                if ($mProfile && !empty($mProfile['rate-limit'])) {
                    if (str_contains($mProfile['rate-limit'], '/')) {
                        [$upload, $download] = explode('/', $mProfile['rate-limit'], 2);
                    }
                }
            }

            $rateLimit = $upload . '/' . $download;

            if ($this->isEditing) {
                $package = \App\Models\Package::find($this->editId);
                
                $package->update([
                    'name' => $this->name,
                    'mikrotik_profile' => $profileName,
                    'price' => $this->price ?: 0,
                    'speed_upload' => $upload,
                    'speed_download' => $download,
                ]);

                if ($this->sync_mode === 'new') {
                    // Sync ke Mikrotik via Job
                    \App\Jobs\ProvisionPackageJob::dispatch($package, 'update', $package->mikrotik_profile, [
                        'local_address' => $this->local_address,
                        'remote_address' => $this->remote_address,
                    ]);
                }

                session()->flash('message', "Profil PPP berhasil diperbarui (Antrian).");
            } else {
                $package = \App\Models\Package::create([
                    'user_id' => auth()->id(),
                    'router_id' => $this->router_id,
                    'tipe' => 'PPPOE',
                    'name' => $this->name,
                    'mikrotik_profile' => $profileName,
                    'price' => $this->price ?: 0,
                    'speed_upload' => $upload,
                    'speed_download' => $download,
                ]);

                if ($this->sync_mode === 'new') {
                    \App\Jobs\ProvisionPackageJob::dispatch($package, 'create', null, [
                        'local_address' => $this->local_address,
                        'remote_address' => $this->remote_address,
                    ]);
                }

                session()->flash('message', "Profil PPP berhasil ditambahkan (Antrian).");
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
        return view('livewire.mikrotik-ppp-profile')
            ->layout('layouts.app', ['header' => 'PPPOE']);
    }
}
