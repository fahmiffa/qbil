<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Asset;

class AssetManager extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    
    public $asset_id, $name;
    public $isOpen = false;

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
        $this->openModal();
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            Asset::updateOrCreate(
                ['id' => $this->asset_id],
                [
                    'user_id' => auth()->id(),
                    'name' => $this->name,
                ]
            );

            $this->dispatch('toast', type: 'success', message: $this->asset_id ? 'Asset berhasil diperbarui' : 'Asset baru berhasil ditambahkan');
            
            $this->closeModal();
            $this->resetInputFields();
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            Asset::findOrFail($id)->delete();
            $this->dispatch('toast', type: 'success', message: 'Asset berhasil dihapus');
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    private function resetInputFields()
    {
        $this->asset_id = null;
        $this->name = '';
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
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $totalCount = $query->count();
        $limit = $this->perPage === 'all' ? max(1, $totalCount) : (int) $this->perPage;

        $assets = $query->orderBy('id', 'desc')->paginate($limit);

        return view('livewire.asset-manager', compact('assets'))
            ->layout('layouts.app', ['header' => 'Master Asset']);
    }
}
