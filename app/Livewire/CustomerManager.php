<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Package;
use App\Services\MikrotikService;
use App\Services\ExcelImportService;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerManager extends Component
{
    use WithPagination;

    public $id_pelanggan, $name, $phone, $address, $keterangan, $status = 'active', $customer_id, $due_date;
    public $package_id, $ppp_profile, $username, $password, $service_type = 'static', $ip_address, $mac_address, $dhcp_server;
    public $creation_method = 'buat_baru';
    public $latitude, $longitude;
    public $selectedPool;
    public $ipPools = [];
    public $dhcpServers = [];
    public $pppProfiles = [];
    public $showPassword = false;
    public $search = '';
    public $filterPackage = '';
    public $filterService = '';
    public $perPage = 10;
    public $isOpen = false;

    public function render()
    {
        $query = auth()->user()->customers()->with('package')->orderBy('id', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('id_pelanggan', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterPackage) {
            $query->where('package_id', $this->filterPackage);
        }

        if ($this->filterService) {
            $query->where('service_type', $this->filterService);
        }

        $totalCount = $query->count();
        $customers = ($this->perPage === 'all')
            ? $query->paginate(max(1, $totalCount))
            : $query->paginate((int) $this->perPage);

        $packages = auth()->user()->packages()
            ->where('tipe', match ($this->service_type) {
                'pppoe' => 'PPPOE',
                'static' => 'STATIC',
                default => 'STATIC'
            })
            ->orderBy('name')->get();

        // All packages for filter dropdown (only STATIC and PPPOE)
        $allPackages = auth()->user()->packages()
            ->whereIn('tipe', ['STATIC', 'PPPOE'])
            ->orderBy('name')
            ->get();

        return view('livewire.customer-manager', [
            'customers'    => $customers,
            'packages'     => $packages,
            'allPackages'  => $allPackages,
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

        try {
            $routerId = auth()->user()->router->id ?? 0;
            $mikrotik = $this->getMikrotikService();
            
            // Menggunakan Cache Laravel selama 5 Menit agar tidak nge-lag saat buka modal
            $this->ipPools = \Illuminate\Support\Facades\Cache::remember("mk_pools_{$routerId}", 300, fn() => $mikrotik->getIpPools());
            $this->dhcpServers = \Illuminate\Support\Facades\Cache::remember("mk_dhcp_{$routerId}", 300, fn() => $mikrotik->getDhcpServers());
            $this->pppProfiles = \Illuminate\Support\Facades\Cache::remember("mk_ppp_{$routerId}", 300, fn() => $mikrotik->getPppProfiles());
        } catch (\Exception $e) {
            $this->ipPools = [];
            $this->dhcpServers = [];
            $this->pppProfiles = [];
        }
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    private function resetInputFields()
    {
        $this->customer_id  = '';
        $this->id_pelanggan = '';
        $this->name         = '';
        $this->phone        = '';
        $this->address      = '';
        $this->keterangan   = '';
        $this->status       = 'active';
        $this->due_date     = '';
        $this->package_id   = '';
        $this->ppp_profile  = '';
        $this->username     = '';
        $this->password     = '';
        $this->service_type = 'static';
        $this->creation_method = 'buat_baru';
        $this->ip_address   = '';
        $this->mac_address  = '';
        $this->dhcp_server  = '';
        $this->latitude     = '';
        $this->longitude    = '';
        $this->selectedPool = '';
        $this->showPassword = false;
    }

    public function autoAssignIp()
    {
        if (!$this->selectedPool) {
            session()->flash('error', 'Pilih IP Pool terlebih dahulu.');
            return;
        }

        try {
            $mikrotik = $this->getMikrotikService();
            $availableIp = $mikrotik->findAvailableIpInPool($this->selectedPool);

            if ($availableIp) {
                $this->ip_address = $availableIp;
                session()->flash('message', 'IP Address ditemukan: ' . $availableIp);
            } else {
                session()->flash('error', 'Tidak ada IP kosong di pool ini.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mencari IP: ' . $e->getMessage());
        }
    }

    public function store()
    {
        $this->validate([
            'id_pelanggan' => 'nullable|string|max:50',
            'name'         => 'required|string|max:255',
            'phone'        => 'nullable|string|max:50',
            'address'      => 'nullable|string',
            'keterangan'   => 'nullable|string',
            'status'       => 'required|in:active,suspended',
            'due_date'     => 'nullable|date',
            'username'     => 'nullable|string|max:100',
            'password'     => 'nullable|string|max:100',
            'ppp_profile'  => 'nullable|string|max:100',
            'service_type' => 'required|in:static,pppoe',
            'ip_address'   => 'required_if:service_type,static|nullable|string|max:50',
            'mac_address'  => 'required_if:service_type,static|nullable|string|max:50',
            'dhcp_server'  => 'required_if:service_type,static|nullable|string|max:50',
            'latitude'     => 'nullable|string|max:50',
            'longitude'    => 'nullable|string|max:50',
        ]);

        $normMac = $this->mac_address ? strtoupper(trim(str_replace(['-', ' '], ':', $this->mac_address))) : null;
        $normIp  = $this->ip_address ? trim($this->ip_address) : null;

        $data = [
            'id_pelanggan' => $this->id_pelanggan,
            'name'         => $this->name,
            'phone'        => $this->phone,
            'address'      => $this->address,
            'keterangan'   => $this->keterangan,
            'status'       => $this->status,
            'due_date'     => $this->due_date ?: null,
            'package_id'   => $this->package_id ?: null,
            'ppp_profile'  => $this->ppp_profile ?: null,
            'username'     => $this->username ?: null,
            'password'     => $this->password ?: null,
            'service_type' => $this->service_type,
            'creation_method' => 'buat_baru',
            'ip_address'   => $normIp,
            'mac_address'  => $normMac,
            'dhcp_server'  => $this->dhcp_server ?: 'all',
            'latitude'     => $this->latitude,
            'longitude'    => $this->longitude,
            'user_id'      => auth()->id(),
            'activated_at' => $this->status === 'active' ? now() : null,
        ];

        try {
            if ($this->customer_id) {
                $customer = Customer::where('id', $this->customer_id)
                    ->where('user_id', auth()->id())
                    ->firstOrFail();

                $oldStatus  = $customer->status;
                $oldProfile = $customer->package?->mikrotik_profile;
                $oldUsername = $customer->username;
                $oldMac = $customer->mac_address;

                $customer->update($data);
                $customer->refresh(); // Wajib direfresh agar cache relasi $customer->package menampilkan paket yang B aru.

                // Provisioning update
                $this->provisionUpdate($customer, $oldStatus, $oldProfile, $oldUsername, $oldMac);

                session()->flash('message', 'Pelanggan berhasil diperbarui.');
            } else {
                $customer = Customer::create($data);

                // Provisioning create
                $this->provisionCreate($customer);

                session()->flash('message', 'Pelanggan berhasil ditambahkan.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal Sinkronisasi MikroTik: ' . $e->getMessage());
        }

        $this->closeModal();
        $this->resetInputFields();
    }



    private function getMikrotikService(): MikrotikService
    {
        $router = auth()->user()->router;
        if (!$router) {
            throw new \Exception('Konfigurasi Router MikroTik belum diatur.');
        }
        return new MikrotikService($router);
    }

    private function provisionCreate(Customer $customer): void
    {
        $mikrotik = $this->getMikrotikService();
        $package  = $customer->package;
        $rateLimit = $package ? ($package->speed_upload . '/' . $package->speed_download) : '0M/0M';

        if ($customer->service_type === 'static') {
            // Clean up existing to avoid conflicts
            if ($customer->mac_address) $mikrotik->removeDhcpLeaseByMac($customer->mac_address);
            if ($customer->ip_address) $mikrotik->removeDhcpLeaseByIp($customer->ip_address);

            if ($customer->mac_address && $customer->ip_address) {
                // Pass $rateLimit correctly to DHCP lease
                $mikrotik->addDhcpLease($customer->mac_address, $customer->ip_address, $customer->dhcp_server ?: 'all', $customer->name, $rateLimit);
            }
        } else {
            // pppoe
            // Clean up existing
            if ($customer->username) $mikrotik->removePppSecret($customer->username);

            $profile = $customer->ppp_profile ?? $package?->mikrotik_profile ?? 'default';
            if ($customer->username && $customer->password) {
                $mikrotik->addPppSecret($customer->username, $customer->password, $profile, $customer->name);
            }
        }

        if ($customer->status === 'suspended') {
            if ($customer->service_type === 'pppoe') {
                $mikrotik->disablePppSecret($customer->username);
            } elseif ($customer->service_type === 'static') {
                if ($customer->mac_address) {
                    // Disable lease directly
                    $mikrotik->setDhcpLeaseStateByMac($customer->mac_address, true);
                }
            }
        }
    }

    private function provisionUpdate(Customer $customer, string $oldStatus, ?string $oldProfile, ?string $oldUsername, ?string $oldMac): void
    {
        $mikrotik = $this->getMikrotikService();
        $package  = $customer->package;
        $rateLimit = $package ? ($package->speed_upload . '/' . $package->speed_download) : '0M/0M';
        $profile  = $customer->ppp_profile ?? $package?->mikrotik_profile ?? $oldProfile ?? 'default';
        $oldUser = $oldUsername ?: $customer->username;
        $oldM = $oldMac ?: $customer->mac_address;

        if ($customer->service_type === 'pppoe') {
            $mikrotik->updatePppSecret($oldUser, $customer->username, $customer->password, $profile, $customer->name);
        } elseif ($customer->service_type === 'static') {
            if ($customer->mac_address && $customer->ip_address) {
                $mikrotik->updateDhcpLeaseByMac($oldM, $customer->mac_address, $customer->ip_address, $customer->dhcp_server ?: 'all', $customer->name, $rateLimit);
            }
        }

        if ($customer->status === 'suspended') {
            if ($customer->service_type === 'pppoe') {
                $mikrotik->disablePppSecret($customer->username);
            } elseif ($customer->service_type === 'static') {
                if ($customer->mac_address) $mikrotik->setDhcpLeaseStateByMac($customer->mac_address, true);
            }
        } elseif ($customer->status === 'active' && $oldStatus === 'suspended') {
            if ($customer->service_type === 'pppoe') {
                $mikrotik->enablePppSecret($customer->username);
            } elseif ($customer->service_type === 'static') {
                if ($customer->mac_address) $mikrotik->setDhcpLeaseStateByMac($customer->mac_address, false);
            }
        }
    }

    public function edit($id)
    {
        $customer = Customer::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $this->customer_id  = $id;
        $this->id_pelanggan = $customer->id_pelanggan;
        $this->name         = $customer->name;
        $this->phone        = $customer->phone;
        $this->address      = $customer->address;
        $this->keterangan   = $customer->keterangan;
        $this->status       = $customer->status;
        $this->due_date     = $customer->due_date ? $customer->due_date->format('Y-m-d') : '';
        $this->package_id   = $customer->package_id;
        $this->ppp_profile  = $customer->ppp_profile;
        $this->username     = $customer->username;
        $this->password     = $customer->password;
        $this->service_type = $customer->service_type;
        $this->ip_address   = $customer->ip_address;
        $this->mac_address  = $customer->mac_address;
        $this->dhcp_server  = $customer->dhcp_server;
        $this->latitude     = $customer->latitude;
        $this->longitude    = $customer->longitude;
        $this->openModal();
        $this->dispatch('map-updated', ['lat' => $customer->latitude, 'lng' => $customer->longitude]);
    }

    public function delete($id)
    {
        try {
            $customer = Customer::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

            // Remove from Mikrotik before deleting
            try {
                $mikrotik = $this->getMikrotikService();
                if ($customer->service_type === 'pppoe' && $customer->username) {
                    $mikrotik->removePppSecret($customer->username);
                } elseif ($customer->service_type === 'static') {
                    // Cleanup legacy simple queue if any
                    try { $mikrotik->removeSimpleQueue($customer->name); } catch(\Exception $e) {}
                    
                    if ($customer->mac_address) {
                        $mikrotik->removeDhcpLeaseByMac($customer->mac_address);
                    }
                }
            } catch (\Exception $e) {
                logger()->error('Mikrotik delete error: ' . $e->getMessage());
            }

            $customer->delete();
            session()->flash('message', 'Pelanggan berhasil dihapus.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }



    public function togglePassword()
    {
        $this->showPassword = !$this->showPassword;
    }
}
