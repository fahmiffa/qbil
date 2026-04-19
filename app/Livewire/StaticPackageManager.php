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
    public $download_value, $download_unit = 'M', $upload_value, $upload_unit = 'M';
    public $tipe = 'STATIC';
    public $isOpen = false;

    public function render()
    {
        $packages = auth()->user()->packages()
            ->where('tipe', 'STATIC')
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
        $this->download_value = '';
        $this->download_unit = 'M';
        $this->upload_value = '';
        $this->upload_unit = 'M';
        $this->mikrotik_profile = 'default';
        $this->package_id = '';
        $this->tipe = 'STATIC';
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
            'download_value' => 'required|numeric|min:1',
            'upload_value' => 'required|numeric|min:1',
            'mikrotik_profile' => 'nullable|string|max:255',
        ];

        // Bersihkan format titik sebelum validasi
        if (isset($this->price) && !is_numeric($this->price)) {
            $this->price = str_replace('.', '', $this->price);
        }

        $this->validate($rules);

        // Gabungkan Value dan Unit
        $this->speed_download = $this->download_value . $this->download_unit;
        $this->speed_upload = $this->upload_value . $this->upload_unit;

        try {
            // For QUEUE type, we normally don't need to create a profile on Mikrotik side
            // as Simple Queues are created per customer.
            // But we save it to our database.

            $data = [
                'name' => $this->name,
                'price' => $this->price,
                'speed_download' => $this->speed_download,
                'speed_upload' => $this->speed_upload,
                'mikrotik_profile' => $this->name,
                'tipe' => 'STATIC',
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
        $this->tipe = $package->tipe;
        
        $this->openModal();
    }

    public function delete($id)
    {
        Package::where('id', $id)->where('user_id', auth()->id())->delete();
        session()->flash('message', 'Paket berhasil dihapus.');
    }
}
