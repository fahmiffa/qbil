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
use App\Jobs\SyncAllCustomersJob;
use App\Jobs\BulkSyncToMikrotikJob;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use App\Models\ActivityLog;
use App\Traits\ChecksDemoMode;

class CustomerManager extends Component
{
    use WithPagination, ChecksDemoMode;

    public $id_pelanggan, $name, $phone, $phone2, $address, $keterangan, $status = 'active', $customer_id, $due_date, $unique_code;
    public $router_id;
    public $package_id, $username, $password, $ppp_profile, $service_type = 'static', $ip_address, $mac_address, $dhcp_server;
    public $creation_method = 'buat_baru';
    public $sync_mikrotik_id = '';
    public $availableMikrotikData = [];
    public $latitude, $longitude;
    public $asset_id;
    public $selectedPool;
    public $autoAssignError = '';
    public $ipPools = [];
    public $dhcpServers = [];
    public $pppProfiles = [];
    public $showPassword = false;
    public $search = '';
    public $filterPackage = '';
    public $filterService = '';
    public $filterStatus = '';
    public $filterDueDate = '';
    public $filterRouter = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
    public $isOpen = false;
    public $isSyncModalOpen = false;
    public $unmatchedStatic = [];
    public $unmatchedPppoe = [];
    public $is_trial = false;
    public $syncError = '';
    public function render()
    {
        $query = auth()->user()->customers()->with('package')->orderBy($this->sortField, $this->sortDirection);

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

        if ($this->filterDueDate) {
            $query->whereDay('due_date', $this->filterDueDate);
        }

        if ($this->filterRouter) {
            $query->where('router_id', $this->filterRouter);
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

        // All packages for filter dropdown based on active features
        $allowedTipe = [];
        if (auth()->user()->hasFeature('static')) $allowedTipe[] = 'STATIC';
        if (auth()->user()->hasFeature('pppoe')) $allowedTipe[] = 'PPPOE';

        $allPackages = auth()->user()->packages()
            ->whereIn('tipe', $allowedTipe)
            ->orderBy('name')
            ->get();

        // Load pools, dhcp, & ppp profiles jika modal terbuka agar reaktif
        if ($this->isOpen) {
            $this->ipPools = IpPool::where('user_id', auth()->id())->get()->toArray();
            $this->dhcpServers = DhcpServer::where('user_id', auth()->id())->get()->toArray();
        }

        // Assets dikelompokkan per kategori untuk dropdown
        $groupedAssets = \App\Models\Asset::where('user_id', auth()->id())
            ->orderBy('category')->orderBy('name')
            ->get()
            ->groupBy('category');

        // Load routers untuk dropdown pilihan
        $routers = \App\Models\Router::where('user_id', auth()->id())->orderBy('id')->get();

        return view('livewire.customer-manager', [
            'customers'     => $customers,
            'packages'      => $packages,
            'allPackages'   => $allPackages,
            'groupedAssets' => $groupedAssets,
            'routers'       => $routers,
        ])->layout('layouts.app', ['header' => 'Pelanggan']);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->generateIdPelanggan();

        // Sync Network Resources via Job (Background)
        if (auth()->user()->hasFeature('mikrotik') && auth()->user()->router) {
            \App\Jobs\SyncMikrotikResourcesJob::dispatch(auth()->user());
        }

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

    public function updatedFilterDueDate()
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
        $this->phone2       = '';
        $this->keterangan   = '';
        $this->status       = 'active';
        $this->due_date     = '';
        $this->router_id    = auth()->user()->routers()->oldest()->value('id') ?? '';
        $this->package_id   = '';
        $this->ppp_profile  = '';
        $this->username     = '';
        $this->password     = '';
        $this->unique_code  = '';

        // Default service type based on features
        if (auth()->user()->hasFeature('static')) {
            $this->service_type = 'static';
        } elseif (auth()->user()->hasFeature('pppoe')) {
            $this->service_type = 'pppoe';
        } else {
            $this->service_type = 'static';
        }

        $this->creation_method = 'buat_baru';
        $this->sync_mikrotik_id = '';
        $this->availableMikrotikData = [];
        $this->ip_address   = '';
        $this->mac_address  = '';
        $this->dhcp_server  = '';
        $this->latitude     = '';
        $this->longitude    = '';
        $this->asset_id     = '';
        $this->selectedPool = '';
        $this->showPassword = false;
        $this->is_trial     = false;
    }

    public function autoAssignIp()
    {
        $this->autoAssignError = '';

        if (!$this->selectedPool) {
            $this->autoAssignError = 'Pilih IP Pool terlebih dahulu.';
            return;
        }

        try {
            set_time_limit(60);

            $mikrotik = $this->getMikrotikService();
            $availableIp = $mikrotik->findAvailableIpInPool($this->selectedPool);

            if ($availableIp) {
                $this->ip_address = $availableIp;
                $this->dispatch('toast', type: 'success', message: 'IP tersedia ditemukan: ' . $availableIp);
            } else {
                $this->autoAssignError = 'Tidak ada IP kosong di pool "' . $this->selectedPool . '".';
            }
        } catch (\Exception $e) {
            $this->autoAssignError = 'Gagal mencari IP: ' . $e->getMessage();
        }
    }

    public function updatedCreationMethod()
    {
        if ($this->creation_method === 'sinkron') {
            $this->loadMikrotikDataForSync();
        }
        $this->sync_mikrotik_id = '';
    }

    public function updatedServiceType()
    {
        if ($this->creation_method === 'sinkron') {
            $this->loadMikrotikDataForSync();
        }
        $this->sync_mikrotik_id = '';
    }

    public function loadMikrotikDataForSync()
    {
        try {
            $mikrotik = $this->getMikrotikService();
            $routerId = auth()->user()->router->id ?? 0;

            if ($this->service_type === 'static') {
                $leases = \Illuminate\Support\Facades\Cache::remember("mk_dhcp_leases_{$routerId}", 300, fn() => $mikrotik->getDhcpLeases());
                $this->availableMikrotikData = [];
                foreach ($leases as $lease) {
                    if (isset($lease['mac-address']) || isset($lease['address'])) {
                        $comment = $lease['comment'] ?? '';
                        $hostname = $lease['host-name'] ?? '-';
                        $label = ($lease['address'] ?? '') . ' - ' . ($lease['mac-address'] ?? '');
                        if ($comment) {
                            $label .= ' (' . $comment . ')';
                        } else {
                            $label .= ' (' . $hostname . ')';
                        }

                        $this->availableMikrotikData[] = [
                            'id' => $lease['.id'] ?? '',
                            'mac_address' => $lease['mac-address'] ?? '',
                            'ip_address' => $lease['address'] ?? '',
                            'dhcp_server' => $lease['server'] ?? '',
                            'hostname' => $lease['host-name'] ?? '',
                            'comment' => $comment,
                            'label' => $label
                        ];
                    }
                }
            } else {
                $secrets = \Illuminate\Support\Facades\Cache::remember("mk_ppp_secrets_{$routerId}", 300, fn() => $mikrotik->getPppSecrets());
                $this->availableMikrotikData = [];
                foreach ($secrets as $secret) {
                    if (isset($secret['name'])) {
                        $comment = $secret['comment'] ?? '';
                        $label = $secret['name'];
                        if ($comment) {
                            $label .= ' - ' . $comment;
                        }
                        $label .= ' (Profile: ' . ($secret['profile'] ?? '-') . ')';

                        $this->availableMikrotikData[] = [
                            'id' => $secret['.id'] ?? '',
                            'username' => $secret['name'] ?? '',
                            'password' => $secret['password'] ?? '',
                            'comment' => $comment,
                            'label' => $label
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            $this->availableMikrotikData = [];
            session()->flash('error', 'Gagal memuat data dari MikroTik: ' . $e->getMessage());
        }
    }

    public function updatedSyncMikrotikId($value)
    {
        if (!$value) return;

        $selected = collect($this->availableMikrotikData)->firstWhere('id', $value);
        if ($selected) {
            if ($this->service_type === 'static') {
                $this->mac_address = strtoupper($selected['mac_address']);
                $this->ip_address = $selected['ip_address'];
                $this->dhcp_server = $selected['dhcp_server'] ?: 'all';
                $suggestedName = $selected['comment'] ?: $selected['hostname'];
                if (empty($this->name) && !empty($suggestedName)) {
                    $this->name = $suggestedName;
                }
            } else {
                $this->username = $selected['username'];
                $this->password = $selected['password'];
                $suggestedName = $selected['comment'] ?: $selected['username'];
                if (empty($this->name)) {
                    $this->name = $suggestedName;
                }
            }
        }
    }

    public function store()
    {
        if ($this->checkDemoMode()) return;

        if (!auth()->user()->hasFeature('mikrotik')) {
            // If no mikrotik feature at all, default to static but it will likely fail later if they try to use mikrotik functions
            $this->service_type = 'static';
        }

        $this->validate([
            'id_pelanggan' => 'nullable|string|max:50',
            'name'         => 'required|string|max:255',
            'phone'        => 'nullable|string|max:50',
            'phone2'       => 'nullable|string|max:50',
            'address'      => 'nullable|string',
            'keterangan'   => 'nullable|string',
            'status'       => 'required|in:active,suspended',
            'due_date'     => 'nullable|date',
            'username'     => 'required_if:service_type,pppoe|nullable|string|max:100',
            'password'     => 'required_if:service_type,pppoe|nullable|string|max:100',
            'service_type' => 'required|in:static,pppoe',
            'ip_address'   => ($this->service_type === 'static' && auth()->user()->hasFeature('static')) ? 'required|string|max:50' : 'nullable',
            'mac_address'  => ($this->service_type === 'static' && auth()->user()->hasFeature('static')) ? 'required|string|max:50|regex:/^([0-9A-Fa-f]{2}[:-]?){5}([0-9A-Fa-f]{2})$/' : 'nullable',
            'dhcp_server'  => ($this->service_type === 'static' && auth()->user()->hasFeature('static')) ? 'required|string|max:50' : 'nullable',
            'latitude'     => 'nullable|string|max:50',
            'longitude'    => 'nullable|string|max:50',
            'package_id'   => 'nullable|exists:packages,id',
        ]);

        $normMac = $this->mac_address ? strtoupper(trim(str_replace(['-', ' '], ':', $this->mac_address))) : null;
        $normIp  = $this->ip_address ? trim($this->ip_address) : null;

        // Cek apakah username sudah ada di MikroTik (Adopsi/Sinkronisasi)
        $isExistingOnMikrotik = false;
        if ($this->service_type === 'pppoe' && auth()->user()->hasFeature('mikrotik') && $this->username) {
            try {
                $mk = $this->getMikrotikService();
                if ($mk->getPppSecretByName($this->username)) {
                    $isExistingOnMikrotik = true;
                }
            } catch (\Exception $e) {
                // Abaikan error koneksi untuk pengecekan ini
            }
        }

        $data = [
            'id_pelanggan' => $this->id_pelanggan,
            'name'         => $this->name,
            'phone'        => $this->phone,
            'phone2'       => $this->phone2,
            'address'      => $this->address,
            'keterangan'   => $this->keterangan,
            'status'       => $this->status,
            'due_date'     => $this->due_date ?: null,
            'router_id'    => $this->router_id ?: null,
            'package_id'   => $this->package_id ?: null,
            'username'     => $this->username ?: null,
            'password'     => $this->password ?: null,
            'service_type' => $this->service_type,
            'creation_method' => $this->creation_method,
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

                // Auto generate unique_code jika belum ada saat di-update
                if (!$customer->unique_code) {
                    $lastUniqueCode = Customer::where('user_id', auth()->id())->max('unique_code') ?? 0;
                    $uniqueCode = $lastUniqueCode + 1;

                    if ($uniqueCode > 999) {
                        throw new \Exception("Alokasi unik kode penuh (> 999).");
                    }
                    $data['unique_code'] = $uniqueCode;
                }

                $customer->update($data);
                $customer->refresh();

                // Dispatch Job untuk provisioning update
                if (auth()->user()->hasFeature('mikrotik')) {
                    \App\Jobs\ProvisionCustomerJob::dispatch($customer, 'update', [
                        'status'      => $oldStatus,
                        'profile'     => $oldProfile,
                        'username'    => $oldUsername,
                        'mac_address' => $oldMac,
                    ]);
                }


                $this->dispatch('toast', type: 'success', message: 'Pelanggan berhasil diperbarui (Sinkronisasi Antrian).');

                // Log Activity
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'title' => 'UPDATE PELANGGAN',
                    'message' => "Memperbarui data pelanggan: {$customer->name} ({$customer->id_pelanggan})",
                    'type' => 'customer_update',
                    'data' => ['customer_id' => $customer->id]
                ]);
            } else {
                // Auto generate unique_code untuk pelanggan baru
                $lastUniqueCode = Customer::where('user_id', auth()->id())->max('unique_code') ?? 0;
                $uniqueCode = $lastUniqueCode + 1;

                if ($uniqueCode > 999) {
                    throw new \Exception("Alokasi unik kode penuh (> 999).");
                }
                $data['unique_code'] = $uniqueCode;

                $customer = Customer::create($data);
                // Dispatch Job untuk Notifikasi WhatsApp Pendaftaran
                \App\Jobs\SendRegistrationWhatsappJob::dispatch($customer);

                $isPascaBayar = auth()->user()->hasFeature('pasca');
                $isSinkron = ($this->creation_method === 'sinkron');

                // Jika data BELUM ada di MikroTik DAN bukan pasca bayar DAN bukan sinkron, jalankan alur pra bayar (Invoice + Notif)
                if (!$isExistingOnMikrotik && !$isPascaBayar && !$isSinkron) {
                    // Generate Invoice Pertama (untuk pendaftaran baru)
                    $firstInvoice = null;
                    try {
                        $invoiceService = new \App\Services\InvoiceService();
                        $firstInvoice = $invoiceService->generateForCustomer($customer, now()->format('Y-m'));
                    } catch (\Exception $e) {
                        Log::error("Gagal generate invoice pertama untuk {$customer->name}: " . $e->getMessage());
                    }

                    
                    
                    // Dispatch Job untuk Notifikasi WhatsApp Tagihan (Invoice) Pertama
                    if ($firstInvoice) {
                        \App\Jobs\SendManualInvoiceWhatsappJob::dispatch($firstInvoice);
                    }
                }
                        
                // Dispatch Job untuk provisioning create
                if (auth()->user()->hasFeature('mikrotik')) {
                    \App\Jobs\ProvisionCustomerJob::dispatch($customer, 'create');
                }

                // TRIAL 30 MENIT: Hanya untuk pra bayar + pendaftaran baru + bukan sinkron + jika CHECKED
                if ($this->is_trial && !$isExistingOnMikrotik && !$isPascaBayar && !$isSinkron && auth()->user()->hasFeature('mikrotik')) {
                    \App\Jobs\IsolateCustomerJob::dispatch($customer)->delay(now()->addMinutes(30));
                }

                if ($isPascaBayar || $isSinkron) {
                    $this->dispatch('toast', type: 'success', message: 'Pelanggan berhasil ditambahkan');
                } else {
                    $this->dispatch('toast', type: 'success', message: 'Pelanggan berhasil ditambahkan. Internet aktif selama 30 menit untuk trial pendaftaran.');
                }

                // Log Activity
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'title' => 'PELANGGAN BARU',
                    'message' => "Menambahkan pelanggan baru: {$customer->name} ({$customer->id_pelanggan})",
                    'type' => 'customer_create',
                    'data' => ['customer_id' => $customer->id]
                ]);

                // Refresh table (kembali ke halaman 1 agar pelanggan baru yang diurutkan 'latest' terlihat)
                $this->resetPage();
            }
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Gagal Memproses Data: ' . $e->getMessage());
        }

        $this->closeModal();
        $this->resetInputFields();
    }

    public function syncAll()
    {
        if ($this->checkDemoMode()) return;

        if (!auth()->user()->hasFeature('mikrotik')) {
            $this->dispatch('toast', type: 'error', message: 'Fitur MikroTik tidak aktif untuk akun Anda.');
            return;
        }

        SyncAllCustomersJob::dispatch(auth()->user());

        $this->dispatch('toast', type: 'success', message: 'Pemeriksaan jatuh tempo & sinkronisasi isolir telah dijadwalkan.');
    }

    public function bulkSyncToMikrotik()
    {
        if ($this->checkDemoMode()) return;

        if (!auth()->user()->hasFeature('mikrotik')) {
            $this->dispatch('toast', type: 'error', message: 'Fitur MikroTik tidak aktif untuk akun Anda.');
            return;
        }

        BulkSyncToMikrotikJob::dispatch(auth()->user());

        $this->dispatch('toast', type: 'success', message: 'Sinkronisasi data ke MikroTik (Bulk Update) telah dijadwalkan.');

        ActivityLog::create([
            'user_id' => auth()->id(),
            'title' => 'BULK SYNC MIKROTIK',
            'message' => "Menjalankan sinkronisasi massal data pelanggan ke MikroTik.",
            'type' => 'bulk_sync',
        ]);
    }

    public function openSyncModal()
    {
        $this->syncError = '';
        $this->isSyncModalOpen = true;
        $this->loadUnmatchedMikrotikData();
    }

    public function closeSyncModal()
    {
        $this->isSyncModalOpen = false;
        $this->unmatchedStatic = [];
        $this->unmatchedPppoe = [];
    }

    public function loadUnmatchedMikrotikData()
    {
        try {
            $mikrotik = $this->getMikrotikService();

            // 1. Get all from Mikrotik
            $leases = $mikrotik->getDhcpLeases();
            $actives = $mikrotik->getPppActives();

            // 2. Get local customers
            $localCustomers = \App\Models\Customer::where('user_id', auth()->id())->get();

            // 3. Find unmatched Static (Leases)
            $localMacs = $localCustomers->where('service_type', 'static')->pluck('mac_address')->filter()->map(function ($mac) {
                return strtoupper(str_replace(['-', ' '], ':', $mac));
            })->toArray();
            $localIps = $localCustomers->where('service_type', 'static')->pluck('ip_address')->filter()->toArray();

            $this->unmatchedStatic = [];
            if (!empty($leases)) {
                foreach ($leases as $lease) {
                    $mac = strtoupper($lease['mac-address'] ?? '');
                    $ip = $lease['address'] ?? '';

                    if (empty($mac) && empty($ip)) continue;

                    if (!in_array($mac, $localMacs) && !in_array($ip, $localIps)) {
                        $this->unmatchedStatic[] = [
                            'mac_address' => $mac,
                            'ip_address' => $ip,
                            'server' => $lease['server'] ?? '',
                            'host_name' => $lease['host-name'] ?? '',
                            'status' => $lease['status'] ?? '',
                            'comment' => $lease['comment'] ?? '',
                        ];
                    }
                }
            }

            // 4. Find unmatched PPPoE (Active)
            $localUsernames = $localCustomers->where('service_type', 'pppoe')->pluck('username')->filter()->toArray();

            $this->unmatchedPppoe = [];
            if (!empty($actives)) {
                foreach ($actives as $active) {
                    $username = $active['name'] ?? '';
                    if (empty($username)) continue;

                    if (!in_array($username, $localUsernames)) {
                        $this->unmatchedPppoe[] = [
                            'username' => $username,
                            'address' => $active['address'] ?? '-',
                            'service' => $active['service'] ?? '-',
                            'uptime' => $active['uptime'] ?? '-',
                            'caller_id' => $active['caller-id'] ?? '-',
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            $this->syncError = $e->getMessage();
        }
    }




    public function edit($id)
    {
        $customer = Customer::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $this->customer_id  = $id;
        $this->id_pelanggan = $customer->id_pelanggan;
        $this->name         = $customer->name;
        $this->phone        = $customer->phone;
        $this->phone2       = $customer->phone2;
        $this->address      = $customer->address;
        $this->keterangan   = $customer->keterangan;
        $this->status       = $customer->status;
        $this->due_date     = $customer->due_date ? $customer->due_date->format('Y-m-d') : '';
        $this->router_id    = $customer->router_id;
        $this->package_id   = $customer->package_id;
        $this->username     = $customer->username;
        $this->password     = $customer->password;
        $this->service_type = $customer->service_type;
        $this->unique_code  = $customer->unique_code;
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
        if ($this->checkDemoMode()) return;

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

            if (auth()->user()->hasFeature('mikrotik')) {
                \App\Jobs\ProvisionCustomerJob::dispatchSync($customer, 'delete');
            }


            $customerName = $customer->name;
            $customerIdPel = $customer->id_pelanggan;
            $customer->delete();
            session()->flash('message', 'Pelanggan berhasil dihapus.');

            // Log Activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'title' => 'HAPUS PELANGGAN',
                'message' => "Menghapus pelanggan: {$customerName} ({$customerIdPel})",
                'type' => 'customer_delete'
            ]);
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
        // Prioritas: router yang dipilih untuk customer ini
        // Fallback: router pertama milik user (backward compatible)
        if ($this->router_id) {
            $router = \App\Models\Router::where('id', $this->router_id)
                ->where('user_id', auth()->id())
                ->first();
        }

        if (empty($router)) {
            $router = auth()->user()->routers()->oldest()->first();
        }

        if (!$router) {
            throw new \Exception('Router MikroTik belum dikonfigurasi.');
        }

        if (!$router->is_active) {
            throw new \Exception("Router '{$router->name}' sedang dinonaktifkan.");
        }

        return MikrotikService::getInstance($router);
    }
}
