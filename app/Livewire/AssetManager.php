<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Asset;
use App\Models\ActivityLog;

class AssetManager extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    
    public $asset_id, $name;
    public $isOpen = false;
    public $category_mode = 'old'; // 'old' or 'new'
    public $selected_category, $new_category, $latitude, $longitude, $address;

    protected $queryString = ['search', 'perPage'];

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function edit($id)
    {
        $asset = Asset::findOrFail($id);
        $this->asset_id = $id;
        $this->name = $asset->name;
        $this->selected_category = $asset->category;
        $this->latitude = $asset->latitude;
        $this->longitude = $asset->longitude;
        $this->address = $asset->address;
        $this->category_mode = 'old';
        $this->openModal();
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'category_mode' => 'required|in:old,new',
            'new_category' => 'required_if:category_mode,new|max:255',
            'selected_category' => 'required_if:category_mode,old|max:255',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        try {
            $category = $this->category_mode === 'new' ? $this->new_category : $this->selected_category;

            Asset::updateOrCreate(
                ['id' => $this->asset_id],
                [
                    'user_id' => auth()->id(),
                    'name' => $this->name,
                    'category' => $category,
                    'latitude' => $this->latitude === '' ? null : $this->latitude,
                    'longitude' => $this->longitude === '' ? null : $this->longitude,
                    'address' => $this->address,
                ]
            );

            $this->dispatch('toast', type: 'success', message: $this->asset_id ? 'Asset berhasil diperbarui' : 'Asset baru berhasil ditambahkan');
            
            // Log Activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'title' => $this->asset_id ? 'UPDATE ASSET' : 'TAMBAH ASSET',
                'message' => ($this->asset_id ? 'Memperbarui' : 'Menambahkan') . " asset: {$this->name} ({$category})",
                'type' => 'asset_crud'
            ]);
            
            $this->closeModal();
            $this->resetInputFields();
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $asset = Asset::findOrFail($id);
            $assetName = $asset->name;
            $asset->delete();
            $this->dispatch('toast', type: 'success', message: 'Asset berhasil dihapus');

            // Log Activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'title' => 'HAPUS ASSET',
                'message' => "Menghapus asset: {$assetName}",
                'type' => 'asset_crud'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    private function resetInputFields()
    {
        $this->asset_id = null;
        $this->name = '';
        $this->selected_category = '';
        $this->new_category = '';
        $this->latitude = '';
        $this->longitude = '';
        $this->address = '';
        $this->category_mode = 'old';
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetInputFields();
        $this->resetValidation();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Asset::where('user_id', auth()->id());

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('category', 'like', '%' . $this->search . '%');
            });
        }

        $totalCount = $query->count();
        $limit = $this->perPage === 'all' ? max(1, $totalCount) : (int) $this->perPage;

        $assets = $query->orderBy('id', 'desc')->paginate($limit);

        $categories = Asset::where('user_id', auth()->id())
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return view('livewire.asset-manager', compact('assets', 'categories'))
            ->layout('layouts.app', ['header' => 'Master Asset']);
    }
}
