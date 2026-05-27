<?php

namespace App\Livewire;

use Livewire\Component;

class MemberManager extends Component
{
    use \Livewire\WithPagination;

    public $name;
    public $whatsapp_number;
    public $editingId = null;
    public $isModalOpen = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'whatsapp_number' => 'nullable|string|max:20',
    ];

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
        $this->whatsapp_number = '';
        $this->editingId = null;
    }

    public function store()
    {
        $this->validate();

        \App\Models\Member::create([
            'user_id' => auth()->id(),
            'name' => $this->name,
            'whatsapp_number' => $this->whatsapp_number,
        ]);

        session()->flash('message', 'Member berhasil ditambahkan.');
        $this->closeModal();
    }

    public function edit($id)
    {
        $member = \App\Models\Member::where('user_id', auth()->id())->findOrFail($id);
        $this->editingId = $id;
        $this->name = $member->name;
        $this->whatsapp_number = $member->whatsapp_number;
        $this->isModalOpen = true;
    }

    public function update()
    {
        $this->validate();

        $member = \App\Models\Member::where('user_id', auth()->id())->findOrFail($this->editingId);
        $member->update([
            'name' => $this->name,
            'whatsapp_number' => $this->whatsapp_number,
        ]);

        session()->flash('message', 'Member berhasil diperbarui.');
        $this->closeModal();
    }

    public function delete($id)
    {
        $member = \App\Models\Member::where('user_id', auth()->id())->findOrFail($id);
        $member->delete();

        session()->flash('message', 'Member berhasil dihapus.');
    }

    public function render()
    {
        $members = \App\Models\Member::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.member-manager', [
            'members' => $members
        ]);
    }
}
