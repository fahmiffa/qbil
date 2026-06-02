<?php

namespace App\Livewire;

use Livewire\Component;

class DiscountManager extends Component
{
    use \Livewire\WithPagination;

    public $name;
    public $type = 'nominal';
    public $amount;
    public $quota = 0;
    public $package_id;

    public $editingId = null;
    public $isModalOpen = false;

    // For preview
    public $selectedPackagePrice = 0;
    public $finalPricePreview = 0;

    protected $rules = [
        'name' => 'required|string|max:255',
        'type' => 'required|in:nominal,percentage',
        'amount' => 'required|numeric|min:0',
        'quota' => 'required|integer|min:0',
        'package_id' => 'required|exists:packages,id',
    ];

    public function updatedPackageId($value)
    {
        $package = \App\Models\Package::where('user_id', auth()->id())->find($value);
        $this->selectedPackagePrice = $package ? $package->price : 0;
        $this->calculatePreview();
    }

    public function updatedType()
    {
        $this->calculatePreview();
    }

    public function updatedAmount()
    {
        $this->calculatePreview();
    }

    private function calculatePreview()
    {
        $amount = (float) ($this->amount ?? 0);
        if ($this->type === 'percentage') {
            $discount = $this->selectedPackagePrice * ($amount / 100);
            $this->finalPricePreview = max(0, $this->selectedPackagePrice - $discount);
        } else {
            $this->finalPricePreview = max(0, $this->selectedPackagePrice - $amount);
        }
    }

    public function openModal()
    {
        $this->resetInputFields();
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->type = 'nominal';
        $this->amount = '';
        $this->quota = 0;
        $this->package_id = '';
        $this->editingId = null;
        $this->selectedPackagePrice = 0;
        $this->finalPricePreview = 0;
    }

    public function store()
    {
        $this->validate();

        \App\Models\Discount::create([
            'user_id' => auth()->id(),
            'name' => $this->name,
            'type' => $this->type,
            'amount' => $this->amount,
            'quota' => $this->quota,
            'package_id' => $this->package_id,
        ]);

        session()->flash('message', 'Diskon berhasil ditambahkan.');
        $this->closeModal();
    }

    public function edit($id)
    {
        $discount = \App\Models\Discount::where('user_id', auth()->id())->findOrFail($id);

        $this->editingId = $discount->id;
        $this->name = $discount->name;
        $this->type = $discount->type;
        $this->amount = $discount->amount + 0; // cast to remove trailing zeros
        $this->quota = $discount->quota;
        $this->package_id = $discount->package_id;

        $this->updatedPackageId($this->package_id); // fetch price and calculate

        $this->isModalOpen = true;
    }

    public function update()
    {
        $this->validate();

        $discount = \App\Models\Discount::where('user_id', auth()->id())->findOrFail($this->editingId);
        $discount->update([
            'name' => $this->name,
            'type' => $this->type,
            'amount' => $this->amount,
            'quota' => $this->quota,
            'package_id' => $this->package_id,
        ]);

        session()->flash('message', 'Diskon berhasil diperbarui.');
        $this->closeModal();
    }

    public function delete($id)
    {
        $discount = \App\Models\Discount::where('user_id', auth()->id())->findOrFail($id);
        $discount->delete();

        session()->flash('message', 'Diskon berhasil dihapus.');
    }

    public function render()
    {
        $discounts = \App\Models\Discount::with('package')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $packages = \App\Models\Package::where('user_id', auth()->id())
            ->where('tipe', 'hotspot')
            ->get();

        return view('livewire.discount-manager', [
            'discounts' => $discounts,
            'packages' => $packages
        ]);
    }
}
