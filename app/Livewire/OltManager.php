<?php

namespace App\Livewire;

use App\Models\Olt;
use App\Models\ActivityLog;
use Livewire\Component;
use Livewire\WithPagination;



class OltManager extends Component
{
    use WithPagination;

    public $name, $ip, $username, $password, $olt_id;
    public $isOpen = false;
    public $search = '';

    public function render()
    {
        $olts = Olt::where('user_id', auth()->id())
            ->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('ip', 'like', '%' . $this->search . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        // Full list for the OLT selector dropdown (no pagination)
        $allOlts = Olt::where('user_id', auth()->id())
            ->orderBy('name')
            ->get(['id', 'name', 'ip']);

        return view('livewire.olt-manager', [
            'olts'    => $olts,
            'allOlts' => $allOlts,
        ])->layout('layouts.app');
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
        $this->ip = '';
        $this->username = '';
        $this->password = '';
        $this->olt_id = '';
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'ip' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        $data = [
            'user_id' => auth()->id(),
            'name' => $this->name,
            'ip' => $this->ip,
            'username' => $this->username,
            'password' => $this->password,
        ];

        if ($this->olt_id) {
            $olt = Olt::findOrFail($this->olt_id);
            $olt->update($data);
            $actionTitle = 'UPDATE OLT';
            $actionMsg = "Memperbarui perangkat OLT: {$this->name} ({$this->ip})";
        } else {
            $olt = Olt::create($data);
            $actionTitle = 'TAMBAH OLT';
            $actionMsg = "Menambahkan perangkat OLT baru: {$this->name} ({$this->ip})";
        }

        // Log Activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'title' => $actionTitle,
            'message' => $actionMsg,
            'type' => 'olt_crud'
        ]);

        session()->flash(
            'message',
            $this->olt_id ? 'OLT device updated successfully.' : 'OLT device created successfully.'
        );

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $olt = Olt::where('user_id', auth()->id())->findOrFail($id);
        $this->olt_id = $id;
        $this->name = $olt->name;
        $this->ip = $olt->ip;
        $this->username = $olt->username;
        $this->password = $olt->password;

        $this->openModal();
    }

    public function delete($id)
    {
        $olt = Olt::where('user_id', auth()->id())->findOrFail($id);
        $oltName = $olt->name;
        $oltIp = $olt->ip;
        $olt->delete();

        // Log Activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'title' => 'HAPUS OLT',
            'message' => "Menghapus perangkat OLT: {$oltName} ({$oltIp})",
            'type' => 'olt_crud'
        ]);

        session()->flash('message', 'OLT device deleted successfully.');
    }
}
