<?php

namespace App\Livewire;

use App\Models\HotspotUser;
use Livewire\Component;
use Livewire\WithPagination;

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
            $package = auth()->user()->packages()->findOrFail($this->package_id);
            $this->profile = $package->mikrotik_profile ?: 'default';
            $oldUsername = null;

            if ($this->hotspot_user_id) {
                $hotspotUser = HotspotUser::findOrFail($this->hotspot_user_id);
                $oldUsername = $hotspotUser->username;
                $hotspotUser->update([
                    'username' => $this->username,
                    'password' => $this->password,
                    'profile' => $this->profile,
                    'package_id' => $this->package_id,
                ]);
                $action = 'update';
            } else {
                $hotspotUser = HotspotUser::create([
                    'user_id' => auth()->id(),
                    'username' => $this->username,
                    'password' => $this->password,
                    'profile' => $this->profile,
                    'package_id' => $this->package_id,
                ]);
                $action = 'create';
            }

            // Dispatch Job
            \App\Jobs\ProvisionHotspotUserJob::dispatch($hotspotUser, $action, $oldUsername);

            session()->flash('message', 'User Hotspot berhasil disimpan (Sinkronisasi Antrian).');
            $this->closeModal();
            $this->resetInputFields();
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal: ' . $e->getMessage());
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
            
            // Dispatch sync for delete to ensure it happens before DB record is gone 
            // OR use dispatch and accept risk OR pass values
            \App\Jobs\ProvisionHotspotUserJob::dispatchSync($user, 'delete');

            $user->delete();
            session()->flash('message', 'User Hotspot dihapus.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}
