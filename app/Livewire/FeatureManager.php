<?php

namespace App\Livewire;

use App\Models\Feature;
use Livewire\Component;
use Livewire\WithPagination;

class FeatureManager extends Component
{
    use WithPagination;

    public $name, $parameter, $feature_id;
    public $isOpen = false;

    public function render()
    {
        $features = Feature::orderBy('id', 'desc')->paginate(10);
        return view('livewire.feature-manager', ['features' => $features])
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
        $this->parameter = '';
        $this->feature_id = '';
    }


    public function store()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'parameter' => 'nullable|string',
        ]);

        Feature::updateOrCreate(['id' => $this->feature_id], [
            'name' => $this->name,
            'parameter' => $this->parameter,
        ]);


        session()->flash('message',
            $this->feature_id ? 'Feature updated successfully.' : 'Feature created successfully.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $feature = Feature::findOrFail($id);
        $this->feature_id = $id;
        $this->name = $feature->name;
        $this->parameter = $feature->parameter;
        
        $this->openModal();
    }


    public function delete($id)
    {
        Feature::find($id)->delete();
        session()->flash('message', 'Feature deleted successfully.');
    }
}
