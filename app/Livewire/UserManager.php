<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class UserManager extends Component
{
    use WithPagination;

    public $name, $email, $phone, $password, $role = 1, $user_id;
    public $isOpen = false;

    public function render()
    {
        $users = User::orderBy('id', 'desc')->paginate(10);
        return view('livewire.user-manager', ['users' => $users])
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
        $this->email = '';
        $this->phone = '';
        $this->password = '';
        $this->role = 1;
        $this->user_id = '';
    }

    public function store()
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users,email' . ($this->user_id ? ',' . $this->user_id : ''),
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:0,1',
        ];

        if (!$this->user_id) {
            $rules['password'] = 'required|min:6';
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->user_id) {
            User::where('id', $this->user_id)->update($data);
        } else {
            User::create($data);
        }

        session()->flash('message',
            $this->user_id ? 'User updated successfully.' : 'User created successfully.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->user_id = $id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->role = $user->role;
        
        $this->openModal();
    }

    public function delete($id)
    {
        if($id === auth()->id()) {
            session()->flash('error', 'Cannot delete yourself.');
            return;
        }
        User::find($id)->delete();
        session()->flash('message', 'User deleted successfully.');
    }
}
