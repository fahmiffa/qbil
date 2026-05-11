<?php

namespace App\Livewire;

use App\Models\Package;
use Livewire\Component;
use Livewire\WithPagination;

class StaticPackageManager extends Component
{
    use WithPagination;

    public $name, $price, $speed_download, $speed_upload, $mikrotik_profile, $package_id;
    public $download_value, $download_unit = 'M', $upload_value, $upload_unit = 'M';
    public $burst_download_value, $burst_download_unit = 'M', $burst_upload_value, $burst_upload_unit = 'M';
    public $burst_threshold, $limit_at, $burst_duration, $priority = 8;
    public $tipe = 'STATIC';
    public $isOpen = false;

    public function render()
    {
        $packages = auth()->user()->packages()
            ->where('tipe', 'STATIC')
            ->orderBy('id', 'desc')
            ->paginate(10);
            
        return view('livewire.static-package-manager', ['packages' => $packages])
            ->layout('layouts.app', ['header' => 'Static']);
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
        $this->burst_download_value = '';
        $this->burst_download_unit = 'M';
        $this->burst_upload_value = '';
        $this->burst_upload_unit = 'M';
        $this->burst_threshold = '';
        $this->limit_at = '';
        $this->burst_duration = '';
        $this->priority = 8;
        $this->mikrotik_profile = 'default';
        $this->package_id = '';
        $this->tipe = 'STATIC';
    }


    public function store()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'download_value' => 'required|numeric|min:1',
            'upload_value' => 'required|numeric|min:1',
            'burst_download_value' => 'nullable|numeric|min:0',
            'burst_upload_value' => 'nullable|numeric|min:0',
            'burst_threshold' => 'nullable|numeric|min:0|max:100',
            'limit_at' => 'nullable|numeric|min:0|max:100',
            'burst_duration' => 'nullable|numeric|min:0',
            'priority' => 'nullable|integer|min:1|max:8',
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
                'burst_download' => $this->burst_download_value ? $this->burst_download_value . $this->burst_download_unit : null,
                'burst_upload' => $this->burst_upload_value ? $this->burst_upload_value . $this->burst_upload_unit : null,
                'burst_threshold' => $this->burst_threshold,
                'limit_at' => $this->limit_at,
                'burst_duration' => $this->burst_duration,
                'priority' => $this->priority,
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

        // Split Value dan Unit untuk burst
        if ($package->burst_download && preg_match('/^(\d+)(K|M)$/i', $package->burst_download, $m)) {
            $this->burst_download_value = $m[1];
            $this->burst_download_unit = strtoupper($m[2]);
        } else {
            $this->burst_download_value = preg_replace('/\D/', '', $package->burst_download);
            $this->burst_download_unit = 'M';
        }

        if ($package->burst_upload && preg_match('/^(\d+)(K|M)$/i', $package->burst_upload, $m)) {
            $this->burst_upload_value = $m[1];
            $this->burst_upload_unit = strtoupper($m[2]);
        } else {
            $this->burst_upload_value = preg_replace('/\D/', '', $package->burst_upload);
            $this->burst_upload_unit = 'M';
        }

        $this->burst_threshold = $package->burst_threshold;
        $this->limit_at = $package->limit_at;
        $this->burst_duration = $package->burst_duration;
        $this->priority = $package->priority ?? 8;
        
        $this->openModal();
    }

    public function delete($id)
    {
        Package::where('id', $id)->where('user_id', auth()->id())->delete();
        session()->flash('message', 'Paket berhasil dihapus.');
    }
}
