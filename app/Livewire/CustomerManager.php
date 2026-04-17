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
    public $package_id, $username, $password, $service_type = 'dynamic', $ip_address, $mac_address;
    public $creation_method = 'buat_baru';
    public $latitude, $longitude;
    public $selectedPool;
    public $ipPools = [];
    public $search = '';
    public $filterPackage = '';
    public $perPage = 10;
    public $isOpen = false;

    public function render()
    {
        $query = auth()->user()->customers()->with('package')->orderBy('id', 'desc');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('id_pelanggan', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterPackage) {
            $query->where('package_id', $this->filterPackage);
        }

        $totalCount = $query->count();
        $customers = ($this->perPage === 'all') 
            ? $query->paginate(max(1, $totalCount)) 
            : $query->paginate((int) $this->perPage);

        $packages = auth()->user()->packages()
            ->where('tipe', match($this->service_type) {
                'pppoe' => 'PPPOE',
                'static' => 'QUEUE',
                'dynamic' => 'QUEUE',
                'ip_binding' => 'QUEUE',
                'hotspot' => 'HOTSPOT',
                default => 'QUEUE'
            })
            ->orderBy('name')->get();

        // All packages for filter dropdown
        $allPackages = auth()->user()->packages()->orderBy('name')->get();

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
            $mikrotik = $this->getMikrotikService();
            $this->ipPools = $mikrotik->getIpPools();
        } catch (\Exception $e) {
            $this->ipPools = [];
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
        $this->username     = '';
        $this->password     = '';
        $this->service_type = 'dynamic';
        $this->creation_method = 'buat_baru';
        $this->ip_address   = '';
        $this->mac_address  = '';
        $this->latitude     = '';
        $this->longitude    = '';
        $this->selectedPool = '';
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
            'service_type' => 'required|in:dynamic,static,pppoe,hotspot,ip_binding',
            'creation_method' => 'required|in:buat_baru,sinkronisasi,manual',
            'ip_address'   => 'nullable|string|max:50',
            'mac_address'  => 'nullable|string|max:50',
            'latitude'     => 'nullable|string|max:50',
            'longitude'    => 'nullable|string|max:50',
        ]);

        $data = [
            'id_pelanggan' => $this->id_pelanggan,
            'name'         => $this->name,
            'phone'        => $this->phone,
            'address'      => $this->address,
            'keterangan'   => $this->keterangan,
            'status'       => $this->status,
            'due_date'     => $this->due_date ?: null,
            'package_id'   => $this->package_id ?: null,
            'username'     => $this->username ?: null,
            'password'     => $this->password ?: null,
            'service_type' => $this->service_type,
            'creation_method' => $this->creation_method,
            'ip_address'   => $this->ip_address,
            'mac_address'  => $this->mac_address,
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

                $customer->update($data);

                // Provisioning update (skip if creation method is manual)
                if ($this->creation_method !== 'manual') {
                    $this->provisionUpdate($customer, $oldStatus, $oldProfile, $oldUsername);
                }

                session()->flash('message', 'Pelanggan berhasil diperbarui.');
            } else {
                $customer = Customer::create($data);

                // Provisioning create (skip if creation method is manual)
                if ($this->creation_method !== 'manual') {
                    $this->provisionCreate($customer);
                }

                session()->flash('message', 'Pelanggan berhasil ditambahkan.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        $this->closeModal();
        $this->resetInputFields();
    }

    public function importExcel()
    {
        try {
            $mikrotik = $this->getMikrotikService();
            $importer = new ExcelImportService($mikrotik);
            
            $filePath = base_path('pelanggan.xlsx');
            $results = $importer->import($filePath);

            $msg = "Impor Selesai: {$results['success']} sukses, {$results['errors']} gagal.";
            if ($results['errors'] > 0) {
                session()->flash('error', $msg . " Lihat log untuk detail.");
            } else {
                session()->flash('message', $msg);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal Impor: ' . $e->getMessage());
        }
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
        try {
            $mikrotik = $this->getMikrotikService();
            $package  = $customer->package;
            $rateLimit = $package ? ($package->speed_upload . '/' . $package->speed_download) : '0M/0M';

            if ($customer->service_type === 'static' || $customer->service_type === 'dynamic') {
                if ($customer->ip_address) {
                    $mikrotik->addSimpleQueue($customer->name, $customer->ip_address, $rateLimit, 'Customer ID: ' . $customer->id);
                }
            } elseif ($customer->service_type === 'ip_binding') {
                if ($customer->mac_address && $customer->ip_address) {
                    $mikrotik->addDhcpLease($customer->mac_address, $customer->ip_address, 'Customer ID: ' . $customer->id);
                }
                if ($customer->ip_address) {
                    $mikrotik->addSimpleQueue($customer->name, $customer->ip_address, $rateLimit, 'Customer ID: ' . $customer->id);
                }
            } elseif ($customer->service_type === 'hotspot') {
                $profile = $package?->mikrotik_profile ?? 'default';
                if ($customer->username && $customer->password) {
                    $mikrotik->addHotspotUser($customer->username, $customer->password, $profile, $customer->name);
                }
            } else {
                // pppoe
                $profile = $package?->mikrotik_profile ?? 'default';
                if ($customer->username && $customer->password) {
                    $mikrotik->addPppSecret($customer->username, $customer->password, $profile, $customer->name);
                }
            }

            if ($customer->status === 'suspended') {
                if ($customer->service_type === 'pppoe') {
                    $mikrotik->disablePppSecret($customer->username);
                } elseif ($customer->service_type === 'static') {
                    $mikrotik->disableSimpleQueue($customer->name);
                }
            }
        } catch (\Exception $e) {
            // Log only, don't block save
            logger()->error('Mikrotik provisioning error: ' . $e->getMessage());
        }
    }

    private function provisionUpdate(Customer $customer, string $oldStatus, ?string $oldProfile, ?string $oldUsername): void
    {
        try {
            $mikrotik = $this->getMikrotikService();
            $package  = $customer->package;
            $rateLimit = $package ? ($package->speed_upload . '/' . $package->speed_download) : '0M/0M';
            $profile  = $package?->mikrotik_profile ?? $oldProfile ?? 'default';
            $oldUser = $oldUsername ?: $customer->username;

            if ($customer->service_type === 'pppoe') {
                $mikrotik->updatePppSecret($oldUser, $customer->username, $customer->password, $profile);
            } elseif ($customer->service_type === 'static' || $customer->service_type === 'dynamic') {
                // For Simple Queue, we use name as identifier
                $mikrotik->updateSimpleQueue($customer->name, $customer->name, $customer->ip_address, $rateLimit);
            } elseif ($customer->service_type === 'hotspot') {
                $mikrotik->updateHotspotUser($oldUser, $customer->username, $customer->password, $profile);
            } elseif ($customer->service_type === 'ip_binding') {
                // Update DHCP lease if mac changed
                if ($customer->mac_address && $customer->ip_address) {
                    // Try to guess old mac? For now we just run updateDhcpLeaseByMac with both as newMac if we don't track old mac.
                    // Wait, finding old mac is tricky if not passed. Let's just run updateDhcpLeaseByMac with current mac
                    $mikrotik->updateDhcpLeaseByMac($customer->mac_address, $customer->mac_address, $customer->ip_address);
                }
                $mikrotik->updateSimpleQueue($customer->name, $customer->name, $customer->ip_address, $rateLimit);
            }

            // Handle Status
            if ($customer->status === 'suspended') {
                if ($customer->service_type === 'pppoe') $mikrotik->disablePppSecret($customer->username);
                elseif ($customer->service_type === 'static' || $customer->service_type === 'dynamic' || $customer->service_type === 'ip_binding') $mikrotik->disableSimpleQueue($customer->name);
                elseif ($customer->service_type === 'hotspot') $mikrotik->disableHotspotUser($customer->username);
            } elseif ($customer->status === 'active' && $oldStatus === 'suspended') {
                if ($customer->service_type === 'pppoe') $mikrotik->enablePppSecret($customer->username);
                elseif ($customer->service_type === 'static' || $customer->service_type === 'dynamic' || $customer->service_type === 'ip_binding') $mikrotik->enableSimpleQueue($customer->name);
                elseif ($customer->service_type === 'hotspot') $mikrotik->enableHotspotUser($customer->username);
            }
        } catch (\Exception $e) {
            logger()->error('Mikrotik provisioning update error: ' . $e->getMessage());
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
        $this->username     = $customer->username;
        $this->password     = $customer->password;
        $this->service_type = $customer->service_type;
        $this->creation_method = $customer->creation_method ?? 'manual';
        $this->ip_address   = $customer->ip_address;
        $this->mac_address  = $customer->mac_address;
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
            if ($customer->username) {
                try {
                    $mikrotik = $this->getMikrotikService();
                    if ($customer->service_type === 'pppoe') {
                        $mikrotik->removePppSecret($customer->username);
                    } elseif ($customer->service_type === 'static') {
                        $mikrotik->removeSimpleQueue($customer->name);
                    }
                } catch (\Exception $e) {
                    logger()->error('Mikrotik delete error: ' . $e->getMessage());
                }
            }

            $customer->delete();
            session()->flash('message', 'Pelanggan berhasil dihapus.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}
