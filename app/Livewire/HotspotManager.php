<?php

namespace App\Livewire;

use App\Models\HotspotUser;
use Livewire\Component;
use Livewire\WithPagination;
use RouterOS\Client;
use RouterOS\Query;

class HotspotManager extends Component
{
    use WithPagination;

    public $username, $password, $profile, $hotspot_user_id, $package_id;
    public $packages_list = [];
    public $isOpen = false;

    public function mount()
    {
        $this->loadPackages();
    }

    public function render()
    {
        $hotspotUsers = auth()->user()->hotspotUsers()->with('package')->orderBy('id', 'desc')->paginate(10);
        return view('livewire.hotspot-manager', ['hotspotUsers' => $hotspotUsers])
            ->layout('layouts.app');
    }

    public function loadPackages()
    {
        $this->packages_list = auth()->user()->packages()
            ->where('tipe', 'hotspot')
            ->orderBy('name')
            ->get();
    }

    private function getMikrotikClient()
    {
        $router = auth()->user()->router;
        if (!$router) {
            throw new \Exception('Router belum dikonfigurasi.');
        }

        return new Client([
            'host' => $router->host,
            'user' => $router->username,
            'pass' => $router->password,
            'port' => (int) $router->port,
            'timeout' => 5,
        ]);
    }

    public function create()
    {
        $this->loadPackages();
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    private function resetInputFields()
    {
        $this->username = '';
        $this->password = '';
        $this->package_id = null;
        $this->profile = 'default';
        $this->hotspot_user_id = '';
    }

    public function updatedPackageId($value)
    {
        if ($value) {
            $package = auth()->user()->packages()->find($value);
            if ($package) {
                $this->profile = $package->mikrotik_profile ?: 'default';
            }
        }
    }

    public function store()
    {
        $this->validate([
            'username' => 'required',
            'password' => 'required',
            'package_id' => 'required|exists:packages,id',
        ]);

        try {
            $client = $this->getMikrotikClient();
            $package = auth()->user()->packages()->findOrFail($this->package_id);
            $this->profile = $package->mikrotik_profile ?: 'default';

            if ($this->hotspot_user_id) {
                // UPDATE
                $oldUser = HotspotUser::findOrFail($this->hotspot_user_id);
                $query = (new Query('/ip/hotspot/user/print'))->where('name', $oldUser->username);
                $mikrotikUser = $client->query($query)->read();

                if (!empty($mikrotikUser)) {
                    $updateQuery = (new Query('/ip/hotspot/user/set'))
                        ->equal('.id', $mikrotikUser[0]['.id'])
                        ->equal('name', $this->username)
                        ->equal('password', $this->password)
                        ->equal('profile', $this->profile);
                    $client->query($updateQuery)->read();
                } else {
                    $addQuery = (new Query('/ip/hotspot/user/add'))
                        ->equal('name', $this->username)
                        ->equal('password', $this->password)
                        ->equal('profile', $this->profile);
                    $client->query($addQuery)->read();
                }
            } else {
                // CREATE
                $addQuery = (new Query('/ip/hotspot/user/add'))
                    ->equal('name', $this->username)
                    ->equal('password', $this->password)
                    ->equal('profile', $this->profile);
                $client->query($addQuery)->read();
            }

            HotspotUser::updateOrCreate(['id' => $this->hotspot_user_id], [
                'user_id' => auth()->id(),
                'username' => $this->username,
                'password' => $this->password,
                'profile' => $this->profile,
                'package_id' => $this->package_id,
            ]);

            session()->flash('message', 'User Hotspot berhasil disimpan.');
            $this->closeModal();
            $this->resetInputFields();
        } catch (\Exception $e) {
            session()->flash('error', 'Mikrotik Error: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $user = HotspotUser::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $this->hotspot_user_id = $id;
        $this->username = $user->username;
        $this->password = $user->password;
        $this->package_id = $user->package_id;
        $this->profile = $user->profile;
        $this->loadPackages();
        $this->openModal();
    }

    public function delete($id)
    {
        try {
            $user = HotspotUser::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
            $client = $this->getMikrotikClient();

            $query = (new Query('/ip/hotspot/user/print'))->where('name', $user->username);
            $mikrotikUser = $client->query($query)->read();

            if (!empty($mikrotikUser)) {
                $delQuery = (new Query('/ip/hotspot/user/remove'))->equal('.id', $mikrotikUser[0]['.id']);
                $client->query($delQuery)->read();
            }

            $user->delete();
            session()->flash('message', 'User Hotspot dihapus.');
        } catch (\Exception $e) {
            session()->flash('error', 'Mikrotik Error: ' . $e->getMessage());
        }
    }
}
