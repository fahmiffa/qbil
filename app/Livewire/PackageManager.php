<?php

namespace App\Livewire;

use App\Models\Package;
use App\Models\ActivityLog;
use App\Services\MikrotikService;
use Livewire\Component;
use Livewire\WithPagination;
use RouterOS\Client;
use RouterOS\Query;

class PackageManager extends Component
{
    use WithPagination;

    public $name, $price, $speed_download, $speed_upload, $mikrotik_profile, $package_id;
    public $router_id;
    public $download_value, $download_unit = 'M', $upload_value, $upload_unit = 'M';
    public $tipe = 'PPPOE';
    public $isOpen = false;

    public function render()
    {
        $packages = auth()->user()->packages()->orderBy('id', 'desc')->paginate(10);
        $routers  = \App\Models\Router::where('user_id', auth()->id())->orderBy('id')->get();
        return view('livewire.package-manager', ['packages' => $packages, 'routers' => $routers])
            ->layout('layouts.app');
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
        $this->resetValidation();
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->price = '';
        $this->speed_download = '';
        $this->speed_upload = '';
        $this->download_value = '';
        $this->download_unit = 'M';
        $this->upload_value = '';
        $this->upload_unit = 'M';
        $this->mikrotik_profile = '';
        $this->package_id = '';
        $this->router_id = auth()->user()->routers()->oldest()->value('id') ?? '';
        $this->tipe = 'PPPOE';
    }

    private function getMikrotikService(): \App\Services\MikrotikService
    {
        // Prioritas: router_id yang dipilih, fallback ke router pertama
        if ($this->router_id) {
            $router = \App\Models\Router::where('id', $this->router_id)
                ->where('user_id', auth()->id())
                ->first();
        }

        if (empty($router)) {
            $router = auth()->user()->routers()->oldest()->first();
        }

        if (!$router) {
            throw new \Exception('Silakan konfigurasi Router Mikrotik terlebih dahulu.');
        }

        return MikrotikService::getInstance($router);
    }

    public function store()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'download_value' => 'required|numeric|min:1',
            'upload_value' => 'required|numeric|min:1',
            'tipe' => 'required|in:PPPOE,HOTSPOT,STATIC',
        ];

        if (isset($this->price)) {
            $this->price = str_replace('.', '', $this->price);
        }

        $this->validate($rules);

        // Gabungkan Value dan Unit
        $this->speed_download = $this->download_value . $this->download_unit;
        $this->speed_upload = $this->upload_value . $this->upload_unit;

        try {
            $data = [
                'name' => $this->name,
                'price' => $this->price,
                'speed_download' => $this->speed_download,
                'speed_upload' => $this->speed_upload,
                'mikrotik_profile' => $this->name,
                'router_id' => $this->router_id ?: null,
                'tipe' => $this->tipe,
                'user_id' => auth()->id(),
            ];

            $oldProfileName = null;
            if ($this->package_id) {
                $package = Package::findOrFail($this->package_id);
                $oldProfileName = $package->mikrotik_profile;
                $package->update($data);
                $action = 'update';
                $actionTitle = 'UPDATE PAKET';
                $actionMsg = "Memperbarui paket: {$package->name} ({$package->tipe})";
            } else {
                $package = Package::create($data);
                $action = 'create';
                $actionTitle = 'TAMBAH PAKET';
                $actionMsg = "Menambahkan paket baru: {$package->name} ({$package->tipe})";
            }

            // Log Activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'title' => $actionTitle,
                'message' => $actionMsg,
                'type' => 'package_crud'
            ]);

            // Sync ke Mikrotik via Job (Kecuali STATIC)
            if ($this->tipe !== 'STATIC') {
                \App\Jobs\ProvisionPackageJob::dispatch($package, $action, $oldProfileName);
                session()->flash('message', 'Paket berhasil disimpan (Singkronisasi Antrian).');
            } else {
                session()->flash('message', 'Paket Static berhasil disimpan.');
            }

            $this->closeModal();
            $this->resetInputFields();

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal Memproses: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $package = Package::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $this->package_id = $id;
        $this->name = $package->name;
        $this->price = (int)$package->price;
        $this->speed_download = $package->speed_download;
        $this->speed_upload = $package->speed_upload;
        
        // Split Value dan Unit untuk form
        if (preg_match('/^(\d+)(K|M)$/i', $package->speed_download, $m)) {
            $this->download_value = $m[1];
            $this->download_unit = strtoupper($m[2]);
        } else {
            $this->download_value = preg_replace('/\D/', '', $package->speed_download);
            $this->download_unit = 'M';
        }

        if (preg_match('/^(\d+)(K|M)$/i', $package->speed_upload, $m)) {
            $this->upload_value = $m[1];
            $this->upload_unit = strtoupper($m[2]);
        } else {
            $this->upload_value = preg_replace('/\D/', '', $package->speed_upload);
            $this->upload_unit = 'M';
        }

        $this->mikrotik_profile = $package->mikrotik_profile;
        $this->router_id = $package->router_id;
        $this->tipe = $package->tipe;
        
        $this->openModal();
    }

    public function delete($id)
    {
        try {
            $package = Package::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
            
            if ($package->tipe !== 'STATIC') {
                \App\Jobs\ProvisionPackageJob::dispatchSync($package, 'delete');
            }

            $packageName = $package->name;
            $package->delete();
            session()->flash('message', 'Paket berhasil dihapus.');

            // Log Activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'title' => 'HAPUS PAKET',
                'message' => "Menghapus paket: {$packageName}",
                'type' => 'package_crud'
            ]);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    public function syncFromMikrotik()
    {
        try {
            $mikrotik = $this->getMikrotikService();

            // Fetch both PPP profiles and Hotspot profiles
            $pppProfiles     = $mikrotik->getPppProfiles();
            $hotspotProfiles = $mikrotik->getHotspotProfiles();

            $synced = 0;
            $skip   = ['default', 'default-encryption'];

            // Sync PPP Profiles
            foreach ($pppProfiles as $profile) {
                $profileName = $profile['name'] ?? null;
                if (!$profileName || in_array($profileName, $skip)) continue;

                Package::updateOrCreate(
                    ['mikrotik_profile' => $profileName, 'user_id' => auth()->id()],
                    [
                        'name'             => $profileName,
                        'speed_download'   => $profile['rate-limit'] ?? '0M/0M',
                        'speed_upload'     => $profile['rate-limit'] ?? '0M/0M',
                        'mikrotik_profile' => $profileName,
                        'user_id'          => auth()->id(),
                        'price'            => 0,
                        'tipe'             => 'PPPOE',
                    ]
                );
                $synced++;
            }

            // Sync Hotspot Profiles
            foreach ($hotspotProfiles as $profile) {
                $profileName = $profile['name'] ?? null;
                if (!$profileName || in_array($profileName, $skip)) continue;

                Package::updateOrCreate(
                    ['mikrotik_profile' => $profileName, 'user_id' => auth()->id()],
                    [
                        'name'             => $profileName,
                        'speed_download'   => $profile['rate-limit'] ?? '0M/0M',
                        'speed_upload'     => $profile['rate-limit'] ?? '0M/0M',
                        'mikrotik_profile' => $profileName,
                        'user_id'          => auth()->id(),
                        'price'            => 0,
                        'tipe'             => 'HOTSPOT',
                    ]
                );
                $synced++;
            }

            session()->flash('message', "Sinkronisasi berhasil! {$synced} profil ditemukan dari MikroTik. Sesuaikan harga tiap paket.");
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal sinkronisasi: ' . $e->getMessage());
        }
    }
}
