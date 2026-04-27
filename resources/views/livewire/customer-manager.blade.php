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
                        
                        @if(auth()->user()->hasFeature('mikrotik'))
                        <button wire:click="syncAll()" wire:loading.attr="disabled" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg transition-all shadow-sm disabled:opacity-50">
                            <svg wire:loading.remove wire:target="syncAll" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <svg wire:loading wire:target="syncAll" class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Sinkronkan MikroTik
                        </button>
                        @endif
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
                        <div class="relative w-full sm:w-44">
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

                        @if(auth()->user()->hasFeature('mikrotik'))
                        <!-- Filter Layanan -->
                        <div class="relative w-full sm:w-40">
                            <select wire:model.live="filterService" class="w-full pl-3 pr-10 py-2 text-sm border border-gray-300 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none bg-white dark:bg-slate-900 dark:text-slate-300 transition-colors">
                                <option value="">Semua Layanan</option>
                                <option value="static">STATIC</option>
                                <option value="pppoe">PPPoE</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        @endif


                        <!-- Filter Status -->
                        <div class="relative w-full sm:w-40">
                            <select wire:model.live="filterStatus" class="w-full pl-3 pr-10 py-2 text-sm border border-gray-300 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none bg-white dark:bg-slate-900 dark:text-slate-300 transition-colors">
                                <option value="">Semua Status</option>
                                <option value="active">ACTIVE</option>
                                <option value="suspended">SUSPENDED</option>
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
                        <div class="flex items-center justify-center min-h-screen px-4 py-8">
                            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" wire:click="closeModal()"></div>
                            
                            <div class="relative bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-2xl z-10 border border-slate-200 dark:border-slate-700 overflow-hidden transform transition-all"
                                 x-on:click.stop>
                                <!-- Modal Header -->
                                <div class="flex items-center justify-between px-8 py-6 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50">
                                    <div>
                                        <h3 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            </div>
                                            {{ $customer_id ? 'Detail Pelanggan' : 'Registrasi Pelanggan Baru' }}
                                        </h3>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-wider font-semibold">Manajemen Konektivitas & Billing</p>
                                    </div>
                                    <button wire:click="closeModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-500 hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-900/30 transition-all duration-300">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <!-- Modal Body -->
                                <form wire:submit.prevent="store">
                                    <div class="px-8 py-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                            <div>
                                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">ID Pelanggan</label>
                                                <div class="relative flex gap-2">
                                                    <input type="text" wire:model="id_pelanggan" placeholder="EB-XXXX"
                                                        class="flex-1 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors border-l-4 border-l-blue-500">
                                                    <button type="button" wire:click="generateIdPelanggan" class="px-3 bg-slate-100 dark:bg-slate-700 text-slate-500 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                    </button>
                                                </div>
                                                @error('id_pelanggan') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                                            </div>

                                            <!-- Nama -->
                                            <div>
                                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Nama Pelanggan <span class="text-red-500">*</span></label>
                                                <input type="text" wire:model="name" placeholder="Nama Lengkap"
                                                    class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors @error('name') border-red-400 @enderror">
                                                @error('name') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                                            </div>

                                            <!-- No HP -->
                                            <div>
                                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">WhatsApp</label>
                                                <input type="text" wire:model="phone" placeholder="08..."
                                                    class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors">
                                                @error('phone') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                                            </div>

                                            <!-- Status -->
                                            <div>
                                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Status <span class="text-red-500">*</span></label>
                                                <select wire:model="status" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors">
                                                    <option value="active">Active</option>
                                                    <option value="suspended">Suspended</option>
                                                </select>
                                                @error('status') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                                            </div>

                                            <!-- Tgl Jatuh Tempo -->
                                            <div>
                                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Tgl. Jatuh Tempo</label>
                                                <input type="date" wire:model="due_date"
                                                    class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors">
                                            </div>

                                            <!-- Alamat -->
                                            <div class="sm:col-span-2">
                                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Alamat Lengkap</label>
                                                <textarea wire:model="address" placeholder="Masukkan alamat..." rows="2"
                                                    class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors"></textarea>
                                            </div>

                                            @if(auth()->user()->hasFeature('map'))
                                            <!-- Lokasi Map -->
                                            <div class="sm:col-span-2">
                                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Koordinat Lokasi</label>
                                                <div class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 shadow-inner">
                                                    <x-google-map lat="latitude" lng="longitude" />
                                                </div>
                                            </div>
                                            @endif


                                            <!-- Asset / Perangkat Terpasang -->
                                            <div class="sm:col-span-2">
                                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                                                    Titik Jaringan (Asset)
                                                    <span class="font-normal text-slate-400 normal-case ml-1">- Opsional</span>
                                                </label>
                                                <select wire:model="asset_id" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors">
                                                    <option value="">-- Tidak terhubung ke asset --</option>
                                                    @foreach($groupedAssets as $category => $assets)
                                                        <optgroup label="{{ $category }}">
                                                            @foreach($assets as $asset)
                                                                <option value="{{ $asset->id }}">{{ $asset->name }}{{ $asset->address ? ' — ' . \Illuminate\Support\Str::limit($asset->address, 50) : '' }}</option>
                                                            @endforeach
                                                        </optgroup>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="sm:col-span-2">
                                                <div class="flex items-center gap-4 my-2">
                                                    <div class="h-px flex-1 bg-slate-200 dark:bg-slate-700"></div>
                                                    <span class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">Parameter Layanan</span>
                                                    <div class="h-px flex-1 bg-slate-200 dark:bg-slate-700"></div>
                                                </div>
                                            </div>

                                            @if(auth()->user()->hasFeature('mikrotik'))
                                            <!-- Tipe Layanan -->
                                            <div>
                                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Pilih Layanan</label>
                                                <select wire:model.live="service_type" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-orange-500 outline-none transition-colors border-l-4 border-l-orange-500">
                                                    <option value="static">STATIC (IP Queue)</option>
                                                    <option value="pppoe">PPPOE (User & PW)</option>
                                                </select>
                                            </div>
                                            @endif


                                            <!-- Paket -->
                                            <div class="{{ auth()->user()->hasFeature('mikrotik') ? '' : 'sm:col-span-2' }}">
                                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">{{ auth()->user()->hasFeature('mikrotik') ? 'Paket Langganan' : 'Pilih Paket' }}</label>
                                                <select wire:model="package_id" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors">
                                                    <option value="">-- Pilih Paket --</option>
                                                    @foreach($packages as $pkg)
                                                        <option value="{{ $pkg->id }}">{{ $pkg->name }} [{{ $pkg->speed_upload }}/{{ $pkg->speed_download }}] — Rp {{ number_format($pkg->price, 0, ',', '.') }}</option>
                                                    @endforeach
                                                </select>
                                                @error('package_id') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                                            </div>


                                            @if(auth()->user()->hasFeature('mikrotik') && $service_type === 'pppoe')
                                                <!-- PPPOE Config -->
                                                <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6 p-5 bg-indigo-50/30 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-900/30 rounded-3xl">
                                                    <div class="sm:col-span-2">
                                                        <p class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest mb-4">Konfigurasi PPPoE</p>
                                                    </div>

                                                    <div class="sm:col-span-2">
                                                        <label class="block text-xs font-bold text-indigo-500 dark:text-indigo-400 uppercase tracking-widest mb-2">Profile Mikrotik</label>
                                                        <select wire:model="ppp_profile" class="w-full bg-white dark:bg-slate-900 border border-indigo-100 dark:border-indigo-900/30 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                                                            <option value="">-- Default Paket --</option>
                                                            @foreach($pppProfiles as $profile)
                                                                <option value="{{ $profile['name'] }}">{{ $profile['name'] }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('ppp_profile') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                                                    </div>

                                                    <div>
                                                        <label class="block text-xs font-bold text-indigo-500 dark:text-indigo-400 uppercase tracking-widest mb-2">Username PPPoE</label>
                                                        <input type="text" wire:model="username" placeholder="user@ebilling"
                                                            class="w-full bg-white dark:bg-slate-900 border border-indigo-100 dark:border-indigo-900/30 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all font-mono">
                                                        @error('username') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-bold text-indigo-500 dark:text-indigo-400 uppercase tracking-widest mb-2">Password PPPoE</label>
                                                        <div class="relative">
                                                            <input type="{{ $showPassword ? 'text' : 'password' }}" wire:model="password" placeholder="Pass123"
                                                                class="w-full bg-white dark:bg-slate-900 border border-indigo-100 dark:border-indigo-900/30 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all font-mono pr-12">
                                                            @error('password') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                                                            <button type="button" wire:click="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-indigo-500 transition-colors">
                                                                @if($showPassword)
                                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                                                @else
                                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                                @endif
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif(auth()->user()->hasFeature('mikrotik') && $service_type === 'static')
                                                <!-- STATIC Config -->
                                                <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6 p-5 bg-emerald-50/30 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-900/30 rounded-3xl">
                                                    <div class="sm:col-span-2">
                                                        <p class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mb-4">Konfigurasi DHCP & MAC Binding</p>
                                                    </div>

                                                    <!-- Row 1: MAC Address | DHCP Server -->
                                                    <div>
                                                        <label class="block text-xs font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-widest mb-2">MAC Address</label>
                                                        <input type="text" wire:model="mac_address" placeholder="00:00:00:00:00:00"
                                                            class="w-full bg-white dark:bg-slate-900 border border-emerald-100 dark:border-emerald-900/30 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all font-mono uppercase">
                                                        @error('mac_address') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                                                    </div>

                                                    <div>
                                                        <label class="block text-xs font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-widest mb-2">DHCP Server</label>
                                                        <select wire:model="dhcp_server" class="w-full bg-white dark:bg-slate-900 border border-emerald-100 dark:border-emerald-900/30 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                                                            <option value="all">all</option>
                                                            @foreach($dhcpServers as $server)
                                                                <option value="{{ $server['name'] }}">{{ $server['name'] }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('dhcp_server') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                                                    </div>

                                                    <!-- Row 2: Pilih IP Pool | IP Address + Search Button -->
                                                    <div>
                                                        <label class="block text-xs font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-widest mb-2">Pilih IP Pool</label>
                                                        <select wire:model="selectedPool" class="w-full bg-white dark:bg-slate-900 border border-emerald-100 dark:border-emerald-900/30 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                                                            <option value="">-- Pilih Pool --</option>
                                                            @foreach($ipPools as $pool)
                                                                <option value="{{ $pool['name'] }}">{{ $pool['name'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div>
                                                        <label class="block text-xs font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-widest mb-2">IP Address <span class="text-red-500">*</span></label>
                                                        <div class="flex gap-2">
                                                            <input type="text" wire:model="ip_address" placeholder="192.168.x.x"
                                                                class="flex-1 bg-white dark:bg-slate-900 border border-emerald-100 dark:border-emerald-900/30 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all font-mono">
                                                            <button type="button" wire:click="autoAssignIp" wire:loading.attr="disabled" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition-all shadow-md flex items-center justify-center min-w-[50px]">
                                                                <span wire:loading.remove wire:target="autoAssignIp">
                                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                                                </span>
                                                                <svg wire:loading wire:target="autoAssignIp" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                            </button>
                                                        </div>
                                                        @error('ip_address') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Modal Footer -->
                                    <div class="px-8 py-5 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3 rounded-b-3xl">
                                        <button type="button" wire:click="closeModal()"
                                            class="px-5 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 transition-all uppercase tracking-widest">
                                            Batal
                                        </button>
                                        <button type="submit" wire:loading.attr="disabled"
                                            class="px-8 py-2.5 text-sm font-black text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-[0_8px_20px_-4px_rgba(37,99,235,0.4)] hover:shadow-[0_12px_24px_-6px_rgba(37,99,235,0.5)] transform hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-50 min-w-[160px] flex items-center justify-center">
                                            <span wire:loading.remove wire:target="store">Simpan Pelanggan</span>
                                            <div wire:loading.flex wire:target="store" class="items-center justify-center gap-2 whitespace-nowrap">
                                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                <span>Memproses...</span>
                                            </div>
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
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">IP</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Jatuh Tempo</th>
                                @if(auth()->user()->hasFeature('mikrotik'))
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Layanan</th>
                                @endif

                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700 transition-colors">
                            @forelse($customers as $idx => $customer)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400 text-center">{{ $customers->firstItem() + $idx }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-600 dark:text-slate-400">{{ $customer->id_pelanggan ?? '-' }}</td>
                                <td class="whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white p-0">
                                    <a href="{{ route('customers.detail', $customer->id) }}" class="block px-4 py-3 hover:text-blue-600 dark:hover:text-blue-400 transition-colors group" wire:navigate>
                                        <span class="group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $customer->name }}</span>
                                        @if($customer->phone)
                                            <p class="text-[10px] text-gray-400 font-normal mt-0.5">{{ $customer->phone }}</p>
                                        @endif
                                    </a>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-slate-300">
                                    @if($customer->package)
                                        <div class="flex flex-col">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800 w-fit">
                                                {{ $customer->package->name }}
                                            </span>
                                            <span class="text-[10px] text-gray-500 mt-0.5 font-mono">
                                                Speed: {{ $customer->package->speed_upload }}/{{ $customer->package->speed_download }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-gray-400 dark:text-slate-500 italic">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-600 dark:text-slate-400">
                                    {{ $customer->service_type === 'static' ? ($customer->ip_address ?? '-') : '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-gray-500 dark:text-slate-400">
                                    {{ $customer->due_date ? $customer->due_date->format('d/m/Y') : '-' }}
                                </td>
                                @if(auth()->user()->hasFeature('mikrotik'))
                                <td class="px-4 py-3 whitespace-nowrap text-center text-sm">
                                    <span class="px-2 py-0.5 rounded text-xs font-medium {{ $customer->service_type === 'pppoe' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                        {{ strtoupper($customer->service_type) }}
                                    </span>
                                </td>
                                @endif

                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    @if($customer->status === 'active')
                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                    @else
                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Suspended</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center space-x-2">
                                    <a href="{{ route('customers.detail', $customer->id) }}" target="_blank"
                                        class="inline-flex items-center px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded-md transition-all shadow-sm text-sm">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Detail
                                    </a>
                                    <button wire:click="edit({{ $customer->id }})" class="inline-flex items-center px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-white rounded-md transition-all shadow-sm text-sm">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Edit
                                    </button>
                                    <button @click="
                                        Swal.fire({
                                            title: 'Hapus Pelanggan?',
                                            text: 'Anda akan menghapus {{ $customer->name }}. Akun di MikroTik juga akan dihapus secara otomatis.',
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonColor: '#ef4444',
                                            cancelButtonColor: '#64748b',
                                            confirmButtonText: 'Ya, Hapus!',
                                            cancelButtonText: 'Batal',
                                            background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff',
                                            color: document.documentElement.classList.contains('dark') ? '#f1f5f9' : '#0f172a'
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                $wire.delete({{ $customer->id }})
                                            }
                                        })
                                    " class="inline-flex items-center px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded-md transition-all shadow-sm text-sm">
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
    @endpush
</div>
