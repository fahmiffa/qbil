<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Package;
use App\Models\Asset;
use App\Models\IpPool;
use App\Models\DhcpServer;
use App\Models\PppProfile;
use App\Services\MikrotikService;
use App\Services\ExcelImportService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;

class CustomerManager extends Component
{
    use WithPagination;

    public $id_pelanggan, $name, $phone, $address, $keterangan, $status = 'active', $customer_id, $due_date;
    public $package_id, $ppp_profile, $username, $password, $service_type = 'static', $ip_address, $mac_address, $dhcp_server;
    public $creation_method = 'buat_baru';
    public $latitude, $longitude;
    public $asset_id;
    public $selectedPool;
    public $ipPools = [];
    public $dhcpServers = [];
    public $pppProfiles = [];
    public $showPassword = false;
    public $search = '';
    public $filterPackage = '';
    public $filterService = '';
    public $filterStatus = '';
    public $perPage = 10;
    public $isOpen = false;

    public function render()
    {
        $query = auth()->user()->customers()->with('package')->latest();

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
        
        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
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

        // Load pools, dhcp, & ppp profiles jika modal terbuka agar reaktif
        if ($this->isOpen) {
            $this->ipPools = IpPool::where('user_id', auth()->id())->get()->toArray();
            $this->dhcpServers = DhcpServer::where('user_id', auth()->id())->get()->toArray();
            $this->pppProfiles = PppProfile::where('user_id', auth()->id())->get()->toArray();
        }

        // Assets dikelompokkan per kategori untuk dropdown
        $groupedAssets = \App\Models\Asset::where('user_id', auth()->id())
            ->orderBy('category')->orderBy('name')
            ->get()
            ->groupBy('category');

        return view('livewire.customer-manager', [
            'customers'     => $customers,
            'packages'      => $packages,
            'allPackages'   => $allPackages,
            'groupedAssets' => $groupedAssets,
        ])->layout('layouts.app');
    }

    public function create()
    {
        $this->resetInputFields();
        $this->generateIdPelanggan();
        $this->openModal();
    }

    public function generateIdPelanggan()
    {
        $prefix = date('ymd');
        $lastCustomer = Customer::where('user_id', auth()->id())
            ->where('id_pelanggan', 'like', $prefix . '%')
            ->orderBy('id_pelanggan', 'desc')
            ->first();

        if ($lastCustomer) {
            // Ambil 4 digit terakhir dan tambah 1
            $lastNumber = (int) substr($lastCustomer->id_pelanggan, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        $this->id_pelanggan = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function openModal()
    {
        $this->isOpen = true;
        $this->resetValidation();

        // Logic ppp_profiles sudah dipindah ke render agar reaktif
        return;
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function updatedFilterPackage()
    {
        $this->resetPage();
    }

    public function updatedFilterService()
    {
        $this->resetPage();
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
        $this->asset_id     = '';
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
            // Tingkatkan batas waktu eksekusi khusus untuk proses pencarian IP ini
            set_time_limit(60);

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
            'username'     => 'required_if:service_type,pppoe|nullable|string|max:100',
            'password'     => 'required_if:service_type,pppoe|nullable|string|max:100',
            'ppp_profile'  => 'nullable|string|max:100',
            'service_type' => 'required|in:static,pppoe',
            'ip_address'   => 'required_if:service_type,static|nullable|string|max:50',
            'mac_address'  => 'required_if:service_type,static|nullable|string|max:50',
            'dhcp_server'  => 'required_if:service_type,static|nullable|string|max:50',
            'latitude'     => 'nullable|string|max:50',
            'longitude'    => 'nullable|string|max:50',
            'package_id'   => 'nullable|exists:packages,id',
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
            'latitude'     => $this->latitude === '' ? null : $this->latitude,
            'longitude'    => $this->longitude === '' ? null : $this->longitude,
            'asset_id'     => $this->asset_id ?: null,
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
                $customer->refresh();

                // Dispatch Job untuk provisioning update
                \App\Jobs\ProvisionCustomerJob::dispatch($customer, 'update', [
                    'status'      => $oldStatus,
                    'profile'     => $oldProfile,
                    'username'    => $oldUsername,
                    'mac_address' => $oldMac,
                ]);

                session()->flash('message', 'Pelanggan berhasil diperbarui (Sinkronisasi Antrian).');
            } else {
                $customer = Customer::create($data);

                // Generate Invoice Pertama (untuk pendaftaran baru)
                $firstInvoice = null;
                try {
                    $invoiceService = new \App\Services\InvoiceService();
                    $firstInvoice = $invoiceService->generateForCustomer($customer, now()->format('Y-m'));
                } catch (\Exception $e) {
                    Log::error("Gagal generate invoice pertama untuk {$customer->name}: " . $e->getMessage());
                }

                // Dispatch Job untuk provisioning create
                \App\Jobs\ProvisionCustomerJob::dispatch($customer, 'create');
                
                // Dispatch Job untuk Notifikasi WhatsApp Pendaftaran
                \App\Jobs\SendRegistrationWhatsappJob::dispatch($customer);

                // Dispatch Job untuk Notifikasi WhatsApp Tagihan (Invoice) Pertama
                if ($firstInvoice) {
                    \App\Jobs\SendManualInvoiceWhatsappJob::dispatch($firstInvoice);
                }

                // TRIAL 30 MENIT: Jika belum bayar dalam 30 menit, otomatis isolir
                \App\Jobs\IsolateCustomerJob::dispatch($customer)->delay(now()->addMinutes(30));

                session()->flash('message', 'Pelanggan berhasil ditambahkan. Internet aktif selama 30 menit untuk trial pendaftaran.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal Memproses Data: ' . $e->getMessage());
        }

        $this->closeModal();
        $this->resetInputFields();
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
        $this->asset_id     = $customer->asset_id;
        $this->openModal();
        $this->dispatch('map-updated', ['lat' => $customer->latitude, 'lng' => $customer->longitude]);
    }

    public function delete($id)
    {
        try {
            $customer = Customer::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

            // Dispatch Job untuk provisioning delete sebelum hapus data DB
            // Catatan: Karena job ini ShouldQueue, kita harus memastikan data model tetap ada 
            // sampai job diproses, atau kita pass datanya langsung.
            // Namun SerializesModels akan gagal jika data dihapus sebelum job diproses.
            // Solusi: Kita jalankan logic hapus di job dulu, baru hapus di DB? 
            // Atau DispatchSync jika ingin sinkron.
            // Tapi user minta "Job saja", jadi saya asumsikan async. 
            // Saya akan pass array data ke Job daripada model jika ingin hapus DB segera.
            
            // Pilihan terbaik: Soft delete atau DispatchSync. 
            // Namun di sistem billing ini sepertinya tidak ada soft delete.
            // Saya akan dispatch job dengan data yang dibutuhkan.
            
            \App\Jobs\ProvisionCustomerJob::dispatchSync($customer, 'delete');

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

    private function getMikrotikService(): MikrotikService
    {
        $router = auth()->user()->router;
        if (!$router) {
            throw new \Exception('Router MikroTik belum dikonfigurasi.');
        }
        return new MikrotikService($router);
    }
}
