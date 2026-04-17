<?php

namespace App\Livewire;

use App\Models\Package;
use Livewire\Component;
use Livewire\WithPagination;
use RouterOS\Client;
use RouterOS\Query;

class StaticPackageManager extends Component
{
    use WithPagination;

    public $name, $price, $speed_download, $speed_upload, $mikrotik_profile, $package_id;
    public $tipe = 'QUEUE';
    public $isOpen = false;

    public function render()
    {
        $packages = auth()->user()->packages()
            ->where('tipe', 'QUEUE')
            ->orderBy('id', 'desc')
            ->paginate(10);
            
        return view('livewire.static-package-manager', ['packages' => $packages])
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
        $this->mikrotik_profile = 'default';
        $this->package_id = '';
        $this->tipe = 'QUEUE';
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
        ];

        $this->validate($rules);

        try {
            // For QUEUE type, we normally don't need to create a profile on Mikrotik side
            // as Simple Queues are created per customer.
            // But we save it to our database.

            $data = [
                'name' => $this->name,
                'price' => $this->price,
                'speed_download' => $this->speed_download,
                'speed_upload' => $this->speed_upload,
                'mikrotik_profile' => $this->mikrotik_profile ?? 'default',
                'tipe' => 'QUEUE',
                'user_id' => auth()->id(),
            ];

            if ($this->package_id) {
                Package::where('id', $this->package_id)->where('user_id', auth()->id())->update($data);
            } else {
                Package::create($data);
            }

            session()->flash('message', 'Paket Static berhasil disimpan.');
            $this->closeModal();
            $this->resetInputFields();

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menyimpan: ' . $e->getMessage());
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
        Package::where('id', $id)->where('user_id', auth()->id())->delete();
        session()->flash('message', 'Paket berhasil dihapus.');
    }
}
