<?php

namespace App\Livewire;

use App\Services\MikrotikService;
use Livewire\Component;

class MikrotikInterface extends Component
{
    public array $interfaces = [];
    public bool $loading = true;
    public string $error = '';

    // Modal state
    public bool $showModal = false;
    public bool $isEditing = false;
    public string $editId = '';

    // Form fields
    public string $formName    = '';
    public string $formComment = '';
    public string $formType    = 'vlan'; // vlan or bridge
    public string $formVlanId  = '';
    public string $formParent  = '';

    public function mount(): void
    {
        $this->loadInterfaces();
    }

    private function getMikrotik(): MikrotikService
    {
        $router = auth()->user()->router;
        if (!$router) {
            throw new \Exception('Router MikroTik belum dikonfigurasi.');
        }
        return new MikrotikService($router);
    }

    public function loadInterfaces(): void
    {
        try {
            $this->loading = true;
            $this->error = '';
            $mikrotik = $this->getMikrotik();
            $interfaces = $mikrotik->getInterfaces();
            $this->interfaces = collect($interfaces)->map(function($iface) {
                $iface['id'] = $iface['.id'] ?? null;
                $iface['running']  = ($iface['running'] ?? 'false') === 'true';
                $iface['disabled'] = ($iface['disabled'] ?? 'false') === 'true';
                return $iface;
            })->toArray();
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function openCreate(): void
    {
        $this->reset(['editId', 'formName', 'formComment', 'formVlanId', 'formParent']);
        $this->formType  = 'vlan';
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit(string $id): void
    {
        if (empty($this->interfaces)) {
            $this->loadInterfaces();
        }

        $iface = collect($this->interfaces)->firstWhere('id', $id);
        if ($iface) {
            $this->editId      = $id;
            $this->formName    = $iface['name'] ?? '';
            $this->formComment = $iface['comment'] ?? '';
            $this->formType    = $iface['type'] ?? 'ether';
            $this->formVlanId  = $iface['vlan-id'] ?? '';
            $this->formParent  = $iface['interface'] ?? '';
            $this->isEditing   = true;
            $this->showModal   = true;
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate([
            'formName'    => 'required|string|max:100',
            'formComment' => 'nullable|string|max:255',
            'formVlanId'  => $this->formType === 'vlan' && !$this->isEditing ? 'required|integer|min:1|max:4094' : 'nullable',
            'formParent'  => $this->formType === 'vlan' && !$this->isEditing ? 'required|string' : 'nullable',
        ]);

        try {
            $mikrotik = $this->getMikrotik();

            if ($this->isEditing) {
                $mikrotik->setInterfaceComment($this->editId, $this->formComment);
                session()->flash('message', "Interface '{$this->formName}' berhasil diperbarui.");
            } else {
                if ($this->formType === 'vlan') {
                    $mikrotik->addVlan($this->formName, (int)$this->formVlanId, $this->formParent, $this->formComment);
                } else {
                    $mikrotik->addBridge($this->formName, $this->formComment);
                }
                session()->flash('message', "Interface '{$this->formName}' berhasil ditambahkan ke MikroTik.");
            }

            $this->closeModal();
            $this->loadInterfaces();
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function toggleInterface(string $id, bool $currentlyDisabled): void
    {
        try {
            $mikrotik = $this->getMikrotik();
            $mikrotik->setInterfaceDisabled($id, !$currentlyDisabled);
            $this->loadInterfaces();
            session()->flash('message', 'Status interface berhasil diubah.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function delete(string $id, string $type): void
    {
        try {
            if (in_array($type, ['ether', 'wlan'])) {
                session()->flash('error', 'Interface fisik (ether/wlan) tidak dapat dihapus.');
                return;
            }
            $mikrotik = $this->getMikrotik();
            $mikrotik->removeInterface($id, $type);
            $this->loadInterfaces();
            session()->flash('message', 'Interface berhasil dihapus dari MikroTik.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.mikrotik-interface')
            ->layout('layouts.app', ['header' => 'Interface MikroTik']);
    }
}
