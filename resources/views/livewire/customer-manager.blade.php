<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pelanggan') }}
        </h2>
    </x-slot>

    <div class="w-full">
        <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-lg transition-colors">
            <div class="p-4 sm:p-6 text-gray-900 dark:text-gray-100">

                @if (session()->has('message'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('message') }}</span>
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                    <div class="flex flex-wrap gap-2">
                        <button wire:click="create()" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah Pelanggan
                        </button>

                        <!-- <button wire:click="importExcel" wire:loading.attr="disabled" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition-all shadow-sm">
                            <svg wire:loading.remove wire:target="importExcel" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <svg wire:loading wire:target="importExcel" class="animate-spin h-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Sync Excel
                        </button> -->
                    </div>

                    <div class="flex flex-wrap gap-3 w-full sm:w-auto">
                        <!-- Show per page -->
                        <div class="relative w-full sm:w-32">
                            <select wire:model.live="perPage" class="w-full pl-3 pr-10 py-2 text-sm border border-gray-300 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none bg-white dark:bg-slate-900 dark:text-slate-300 transition-colors">
                                <option value="10">Show 10</option>
                                <option value="50">Show 50</option>
                                <option value="100">Show 100</option>
                                <option value="all">Show All</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>

                        <!-- Filter Paket -->
                        <div class="relative w-full sm:w-48">
                            <select wire:model.live="filterPackage" class="w-full pl-3 pr-10 py-2 text-sm border border-gray-300 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none bg-white dark:bg-slate-900 dark:text-slate-300 transition-colors">
                                <option value="">Semua Paket</option>
                                @foreach($allPackages as $pkg)
                                    <option value="{{ $pkg->id }}">{{ $pkg->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>

                        <!-- Search -->
                        <div class="relative w-full sm:w-64">
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari Nama / ID Pelanggan..." 
                                class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm bg-white dark:bg-slate-900 dark:text-slate-300 transition-colors">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                        </div>
                    </div>
                </div>

                @if($isOpen)
                    <div class="fixed z-50 inset-0 overflow-y-auto">
                        <div class="flex items-center justify-center min-h-screen px-4">
                            <div class="fixed inset-0 bg-black opacity-40"></div>
                            <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-2xl z-10 border border-transparent dark:border-slate-700 transition-colors">
                                <!-- Modal Header -->
                                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-slate-700">
                                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">
                                        {{ $customer_id ? 'Edit Pelanggan' : 'Tambah Pelanggan' }}
                                    </h3>
                                    <button wire:click="closeModal()" class="text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300 transition-colors">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <!-- Modal Body -->
                                <form wire:submit.prevent="store">
                                    <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-4 bg-white dark:bg-slate-800 transition-colors">
                                        <!-- ID Pelanggan -->
                                        <div>
                                            <label class="block text-gray-700 dark:text-slate-300 text-sm font-semibold mb-1">ID Pelanggan</label>
                                            <input type="text" wire:model="id_pelanggan" placeholder="Contoh: EB-001"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            @error('id_pelanggan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>

                                        <!-- Nama -->
                                        <div>
                                            <label class="block text-gray-700 dark:text-slate-300 text-sm font-semibold mb-1">Nama Pelanggan <span class="text-red-500">*</span></label>
                                            <input type="text" wire:model="name" placeholder="Masukkan nama lengkap"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-400 @enderror">
                                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>

                                        <!-- No HP -->
                                            <!-- No HP -->
                                            <div>
                                                <label class="block text-gray-700 dark:text-slate-300 text-sm font-semibold mb-1">No. HP / WhatsApp</label>
                                                <input type="text" wire:model="phone" placeholder="6281234567890"
                                                    class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                                                @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>

                                        <!-- Status -->
                                            <!-- Status -->
                                            <div>
                                                <label class="block text-gray-700 dark:text-slate-300 text-sm font-semibold mb-1">Status <span class="text-red-500">*</span></label>
                                                <select wire:model="status" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                                                    <option value="active">Active</option>
                                                    <option value="suspended">Suspended</option>
                                                </select>
                                                @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>

                                        <!-- Jatuh Tempo -->
                                            <!-- Jatuh Tempo -->
                                            <div>
                                                <label class="block text-gray-700 dark:text-slate-300 text-sm font-semibold mb-1">Tgl. Jatuh Tempo</label>
                                                <input type="date" wire:model="due_date"
                                                    class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                                                @error('due_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>

                                        <!-- Alamat -->
                                            <!-- Alamat -->
                                            <div class="sm:col-span-2">
                                                <label class="block text-gray-700 dark:text-slate-300 text-sm font-semibold mb-1">Alamat</label>
                                                <textarea wire:model="address" placeholder="Masukkan alamat lengkap" rows="2"
                                                    class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"></textarea>
                                                @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>

                                        <!-- Keterangan -->
                                            <!-- Keterangan -->
                                            <div class="sm:col-span-2">
                                                <label class="block text-gray-700 dark:text-slate-300 text-sm font-semibold mb-1">Keterangan</label>
                                                <input type="text" wire:model="keterangan" placeholder="Catatan tambahan"
                                                    class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                                                @error('keterangan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>

                                        <!-- Koordinat Peta (Google Maps Component) -->
                                        <div class="sm:col-span-2">
                                            <x-google-map lat="latitude" lng="longitude" />
                                        </div>

                                        <!-- Divider: MikroTik Setup -->
                                        <div class="sm:col-span-2">
                                            <hr class="border-dashed border-gray-300 dark:border-slate-700 my-2">
                                            <p class="text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Konfigurasi MikroTik</p>
                                        </div>

                                        <!-- Metode Pembuatan -->
                                        <div class="sm:col-span-2">
                                            <label class="block text-gray-700 dark:text-slate-300 text-sm font-semibold mb-1">Metode Pembuatan</label>
                                            <select wire:model.live="creation_method" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                                                <option value="buat_baru">Buat Baru</option>
                                                <option value="sinkronisasi">Sinkronisasi</option>
                                                <option value="manual">Manual</option>
                                            </select>
                                            <p class="text-xs text-gray-400 mt-1 italic leading-relaxed">
                                                @if($creation_method === 'buat_baru') 👉 Input pelanggan dari awal di sistem billing. Akan digenerate otomatis ke Router.
                                                @elseif($creation_method === 'sinkronisasi') 👉 Ambil data yang sudah ada dari perangkat (seperti OLT / MikroTik) ke billing. Cocok kalau sebelumnya sudah disetting di Router.
                                                @elseif($creation_method === 'manual') 👉 Input sendiri satu-satu tanpa ada auto generate ke Router. Biasanya untuk kasus khusus.
                                                @endif
                                            </p>
                                        </div>

                                        <!-- Tipe Layanan -->
                                        <div class="sm:col-span-2 mt-2">
                                            <label class="block text-gray-700 dark:text-slate-300 text-sm font-semibold mb-1">Tipe Layanan</label>
                                            <select wire:model.live="service_type" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                                                <option value="dynamic">Dynamic</option>
                                                <option value="static">Static</option>
                                                <option value="pppoe">PPPoE</option>
                                                <option value="hotspot">Hotspot</option>
                                                <option value="ip_binding">IP Bindings</option>
                                            </select>
                                            <p class="text-xs text-gray-400 mt-1 italic leading-relaxed">
                                                @if($service_type === 'dynamic') 👉 IP pelanggan otomatis (DHCP). Umum dipakai, simpel, tidak perlu login.
                                                @elseif($service_type === 'static') 👉 IP tetap (tidak berubah). Biasanya untuk kantor / pelanggan khusus.
                                                @elseif($service_type === 'pppoe') 👉 Pakai username & password. Paling sering dipakai ISP karena bisa kontrol user.
                                                @elseif($service_type === 'hotspot') 👉 Login lewat halaman web (voucher/user). Cocok untuk wifi publik.
                                                @elseif($service_type === 'ip_binding') 👉 Mengikat IP ke MAC address tertentu. Lebih ke kontrol keamanan / bypass hotspot.
                                                @endif
                                            </p>
                                            @error('service_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>

                                        <!-- Paket -->
                                        <div>
                                            <label class="block text-gray-700 dark:text-slate-300 text-sm font-semibold mb-1">Paket Internet</label>
                                            <select wire:model="package_id" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="">-- Pilih Paket --</option>
                                                @foreach($packages as $pkg)
                                                    <option value="{{ $pkg->id }}">{{ $pkg->name }} ({{ $pkg->mikrotik_profile }}) — Rp {{ number_format($pkg->price, 0, ',', '.') }}</option>
                                                @endforeach
                                            </select>
                                            @error('package_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>

                                        @if($service_type === 'pppoe' || $service_type === 'hotspot')
                                            <!-- Username MikroTik -->
                                            <div>
                                                <label class="block text-gray-700 dark:text-slate-300 text-sm font-semibold mb-1">Username MikroTik</label>
                                                <input type="text" wire:model="username" placeholder="Contoh: pelanggan01"
                                                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                @error('username') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>

                                            <!-- Password MikroTik -->
                                            <div>
                                                <label class="block text-gray-700 dark:text-slate-300 text-sm font-semibold mb-1">Password MikroTik</label>
                                                <input type="text" wire:model="password" placeholder="Kosongkan jika tidak diubah"
                                                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>
                                        @else
                                            <!-- IP Pool Selection (Auto Assign) -->
                                            <div>
                                                <label class="block text-gray-700 dark:text-slate-300 text-sm font-semibold mb-1">Pilih IP Pool</label>
                                                <select wire:model="selectedPool" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                                                    <option value="">-- Pilih Pool MikroTik --</option>
                                                    @foreach($ipPools as $pool)
                                                        <option value="{{ $pool['name'] }}">{{ $pool['name'] }} ({{ $pool['ranges'] }})</option>
                                                    @endforeach
                                                </select>
                                                <p class="text-[10px] text-gray-400 mt-1 italic">Opsional: Pilih pool untuk mencari IP kosong.</p>
                                            </div>

                                            <!-- IP Address -->
                                            <div>
                                                <label class="block text-gray-700 dark:text-slate-300 text-sm font-semibold mb-1">IP Address</label>
                                                <div class="flex gap-2">
                                                    <input type="text" wire:model="ip_address" placeholder="Contoh: 192.168.88.10"
                                                        class="flex-1 border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                                                    <button type="button" wire:click="autoAssignIp" wire:loading.attr="disabled" 
                                                        class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-all whitespace-nowrap flex items-center justify-center min-w-[80px]">
                                                        <span wire:loading.remove wire:target="autoAssignIp">Cari IP</span>
                                                        <svg wire:loading wire:target="autoAssignIp" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                    </button>
                                                </div>
                                                @error('ip_address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>

                                            @if($service_type === 'ip_binding')
                                            <!-- MAC Address -->
                                            <div class="sm:col-span-2">
                                                <label class="block text-gray-700 dark:text-slate-300 text-sm font-semibold mb-1">MAC Address <span class="text-xs text-gray-400 font-normal">(Wajib untuk IP Binding)</span></label>
                                                <input type="text" wire:model="mac_address" placeholder="Contoh: 00:1A:2B:3C:4D:5E"
                                                    class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors uppercase">
                                                @error('mac_address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </div>
                                            @endif
                                        @endif

                                        @if(!$customer_id && ($creation_method === 'buat_baru' || $creation_method === 'sinkronisasi'))
                                        <div class="sm:col-span-2">
                                            <p class="text-xs text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg px-3 py-2">
                                                💡 Jika paket dipilih dan metode Sinkronisasi/Buat Baru, profil akun akan diprovisi otomatis ke MikroTik saat disimpan.
                                            </p>
                                        </div>
                                        @endif
                                    </div>

                                    <!-- Modal Footer -->
                                    <div class="px-6 py-4 border-t border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-800 flex justify-end gap-3 rounded-b-2xl transition-colors">
                                        <button type="button" wire:click="closeModal()"
                                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-slate-300 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 rounded-lg transition-all">
                                            Batal
                                        </button>
                                        <button type="submit"
                                            class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-all">
                                            Simpan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-slate-700 shadow-sm transition-colors">
                    <table class="min-w-full min-w-[900px] divide-y divide-gray-200 dark:divide-slate-700">
                        <thead class="bg-gray-50 dark:bg-slate-900/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">ID Pelanggan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Nama</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Paket</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">User / IP</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Jatuh Tempo</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Layanan</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700 transition-colors">
                            @forelse($customers as $idx => $customer)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400 text-center">{{ $customers->firstItem() + $idx }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-600 dark:text-slate-400">{{ $customer->id_pelanggan ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $customer->name }}
                                    @if($customer->phone)
                                        <p class="text-[10px] text-gray-400 font-normal">{{ $customer->phone }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-slate-300">
                                    @if($customer->package)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                            {{ $customer->package->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 dark:text-slate-500 italic">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-600 dark:text-slate-400">
                                    {{ $customer->service_type === 'static' ? ($customer->ip_address ?? '-') : ($customer->username ?? '-') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-gray-500 dark:text-slate-400">
                                    {{ $customer->due_date ? $customer->due_date->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center text-sm">
                                    <span class="px-2 py-0.5 rounded text-xs font-medium {{ $customer->service_type === 'pppoe' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                        {{ strtoupper($customer->service_type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    @if($customer->status === 'active')
                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                    @else
                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Suspended</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center space-x-2">
                                    <button wire:click="edit({{ $customer->id }})" class="inline-flex items-center px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-white rounded-md transition-all shadow-sm text-sm">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Edit
                                    </button>
                                    <button wire:click="delete({{ $customer->id }})" wire:confirm="Yakin ingin menghapus pelanggan ini? Akun di MikroTik juga akan dihapus." class="inline-flex items-center px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded-md transition-all shadow-sm text-sm">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-gray-500 dark:text-slate-500 italic">Belum ada data pelanggan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($customers->hasPages() && $perPage !== 'all')
                    <div class="mt-6 px-4 py-3 bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 sm:px-6 rounded-b-lg shadow-sm text-gray-700 dark:text-slate-400">
                        {{ $customers->links() }}
                    </div>
                @endif

            </div>
        </div>
    </div>

    @push('scripts')
    <!-- Pastikan Anda menambahkan GOOGLE_MAPS_API_KEY di file .env -->
    <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&loading=async&libraries=places,marker"></script>
    @endpush
</div>
