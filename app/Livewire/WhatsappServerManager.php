<?php

namespace App\Livewire;

use App\Models\WhatsappServer;
use App\Models\ActivityLog;
use Livewire\Component;
use Livewire\WithPagination;

class WhatsappServerManager extends Component
{
    use WithPagination;

    public $name, $api_url, $socket_url, $server_id;
    public $isOpen = false;

    public function render()
    {
        // Only allow role 0 (Super Admin)
        if (auth()->user()->role != 0) {
            abort(403);
        }

        $servers = WhatsappServer::orderBy('id', 'desc')->paginate(10);
        return view('livewire.whatsapp-server-manager', ['servers' => $servers])
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
        $this->api_url = '';
        $this->socket_url = '';
        $this->server_id = '';
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'api_url' => 'required|url|max:255',
            'socket_url' => 'required|url|max:255',
        ]);

        $server = WhatsappServer::updateOrCreate(['id' => $this->server_id], [
            'name' => $this->name,
            'api_url' => $this->api_url,
            'socket_url' => $this->socket_url,
        ]);

        $actionTitle = $this->server_id ? 'UPDATE WA SERVER' : 'TAMBAH WA SERVER';
        $actionMsg = $this->server_id ? "Memperbarui server WA: {$server->name}" : "Menambahkan server WA baru: {$server->name}";

        // Log Activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'title' => $actionTitle,
            'message' => $actionMsg,
            'type' => 'user_crud'
        ]);

        session()->flash('message', 
            $this->server_id ? 'Server updated successfully.' : 'Server created successfully.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $server = WhatsappServer::findOrFail($id);
        $this->server_id = $id;
        $this->name = $server->name;
        $this->api_url = $server->api_url;
        $this->socket_url = $server->socket_url;
        
        $this->openModal();
    }

    public function delete($id)
    {
        $server = WhatsappServer::findOrFail($id);
        $serverName = $server->name;
        $server->delete();

        // Log Activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'title' => 'HAPUS WA SERVER',
            'message' => "Menghapus server WA: {$serverName}",
            'type' => 'user_crud'
        ]);

        session()->flash('message', 'Server deleted successfully.');
    }
}
