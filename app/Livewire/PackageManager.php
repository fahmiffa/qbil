<?php

namespace App\Livewire;

use App\Models\Package;
use Livewire\Component;
use Livewire\WithPagination;
use RouterOS\Client;
use RouterOS\Query;

class PackageManager extends Component
{
    use WithPagination;

    public $name, $price, $speed_download, $speed_upload, $mikrotik_profile, $package_id;
    public $tipe = 'PPPOE';
    public $isOpen = false;

    public function render()
    {
        $packages = auth()->user()->packages()->orderBy('id', 'desc')->paginate(10);
        return view('livewire.package-manager', ['packages' => $packages])
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
        $this->mikrotik_profile = '';
        $this->package_id = '';
        $this->tipe = 'PPPOE';
    }

    private function getMikrotikClient()
    {
        $router = auth()->user()->router;
        if (!$router) {
            throw new \Exception('Silakan konfigurasi Router Mikrotik terlebih dahulu.');
        }

        return new Client([
            'host' => $router->host,
            'user' => $router->username,
            'pass' => $router->password,
            'port' => (int) $router->port,
            'timeout' => 5,
        ]);
    }

    public function store()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'speed_download' => 'required|string|max:50',
            'speed_upload' => 'required|string|max:50',
            'mikrotik_profile' => 'nullable|string|max:255',
            'tipe' => 'required|in:PPPOE,HOTSPOT,QUEUE',
        ];

        $this->validate($rules);

        try {
            $client = $this->getMikrotikClient();
            $rateLimit = $this->speed_upload . '/' . $this->speed_download;

            if ($this->tipe !== 'QUEUE') {
                if ($this->package_id) {
                    // UPDATE
                    $oldPackage = Package::findOrFail($this->package_id);
                    
                    // Cari profile lama di Mikrotik (tergantung tipe)
                    $path = $this->tipe === 'HOTSPOT' ? '/ip/hotspot/user/profile' : '/ppp/profile';
                    $query = (new Query($path . '/print'))->where('name', $oldPackage->mikrotik_profile);
                    $profile = $client->query($query)->read();

                    if (!empty($profile)) {
                        $updateQuery = (new Query($path . '/set'))
                            ->equal('.id', $profile[0]['.id'])
                            ->equal('name', $this->mikrotik_profile)
                            ->equal('rate-limit', $rateLimit);
                        $client->query($updateQuery)->read();
                    } else {
                        // Jika tidak ketemu di mikrotik, buat baru
                        $addQuery = (new Query($path . '/add'))
                            ->equal('name', $this->mikrotik_profile)
                            ->equal('rate-limit', $rateLimit);
                        $client->query($addQuery)->read();
                    }
                } else {
                    // CREATE NEW
                    $path = $this->tipe === 'HOTSPOT' ? '/ip/hotspot/user/profile' : '/ppp/profile';
                    $addQuery = (new Query($path . '/add'))
                        ->equal('name', $this->mikrotik_profile)
                        ->equal('rate-limit', $rateLimit);
                    $client->query($addQuery)->read();
                }
            }

            $data = [
                'name' => $this->name,
                'price' => $this->price,
                'speed_download' => $this->speed_download,
                'speed_upload' => $this->speed_upload,
                'mikrotik_profile' => $this->mikrotik_profile,
                'tipe' => $this->tipe,
                'user_id' => auth()->id(),
            ];

            if ($this->package_id) {
                Package::where('id', $this->package_id)->where('user_id', auth()->id())->update($data);
            } else {
                Package::create($data);
            }

            session()->flash('message', 'Paket berhasil disimpan dan disinkronkan ke Mikrotik.');
            $this->closeModal();
            $this->resetInputFields();

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal Sinkron Mikrotik: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $package = Package::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $this->package_id = $id;
        $this->name = $package->name;
        $this->price = $package->price;
        $this->speed_download = $package->speed_download;
        $this->speed_upload = $package->speed_upload;
        $this->mikrotik_profile = $package->mikrotik_profile;
        $this->tipe = $package->tipe;
        
        $this->openModal();
    }

    public function delete($id)
    {
        try {
            $package = Package::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
            $client = $this->getMikrotikClient();
            
            if ($package->tipe !== 'QUEUE') {
                $path = $package->tipe === 'HOTSPOT' ? '/ip/hotspot/user/profile' : '/ppp/profile';
                $query = (new Query($path . '/print'))->where('name', $package->mikrotik_profile);
                $profile = $client->query($query)->read();

                if (!empty($profile)) {
                    $delQuery = (new Query($path . '/remove'))->equal('.id', $profile[0]['.id']);
                    $client->query($delQuery)->read();
                }
            }

            $package->delete();
            session()->flash('message', 'Paket berhasil dihapus dari sistem dan Mikrotik.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus di Mikrotik: ' . $e->getMessage());
        }
    }

    public function syncFromMikrotik()
    {
        try {
            $client = $this->getMikrotikClient();

            // Fetch both PPP profiles and Hotspot profiles
            $pppProfiles     = $client->query(new Query('/ppp/profile/print'))->read();
            $hotspotProfiles = $client->query(new Query('/ip/hotspot/user/profile/print'))->read();

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
