<div>
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
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Pelanggan
                        </button>

                        @if(auth()->user()->hasFeature('mikrotik'))
                        <div class="flex gap-2">
                            <button type="button" wire:click="openSyncModal" wire:loading.attr="disabled" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg transition-all shadow-sm disabled:opacity-50">
                                <svg wire:loading.remove wire:target="openSyncModal" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <svg wire:loading wire:target="openSyncModal" class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Ambil Data MikroTik
                            </button>

                            <button type="button" @click="
                                Swal.fire({
                                    title: 'Update Massal ke MikroTik?',
                                    text: 'Aplikasi akan mengirim ulang data (Lease & PPP Secret) seluruh pelanggan ke MikroTik agar sesuai dengan database.',
                                    icon: 'info',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3b82f6',
                                    cancelButtonColor: '#64748b',
                                    confirmButtonText: 'Ya, Sinkronkan!',
                                    cancelButtonText: 'Batal',
                                    background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff',
                                    color: document.documentElement.classList.contains('dark') ? '#f1f5f9' : '#0f172a'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $wire.bulkSyncToMikrotik()
                                    }
                                })
                            " class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-all shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                </svg>
                                Push ke MikroTik
                            </button>
                        </div>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-3 w-full sm:w-auto">
                        <!-- Show per page -->
                        <div class="relative w-full sm:w-32">
                            <select wire:model.live="perPage" class="w-full pl-3 pr-10 py-2 text-sm border border-gray-300 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none bg-white dark:bg-slate-900 dark:text-slate-300 transition-colors">
                                <option value="10">Tampil 10</option>
                                <option value="50">Tampil 50</option>
                                <option value="100">Tampil 100</option>
                                <option value="all">Tampil Semua</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        @if(auth()->user()->hasFeature('mikrotik'))
                        <!-- Filter Router -->
                        <div class="relative w-full sm:w-44">
                            <select wire:model.live="filterRouter" class="w-full pl-3 pr-10 py-2 text-sm border border-gray-300 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none bg-white dark:bg-slate-900 dark:text-slate-300 transition-colors">
                                <option value="">Semua Router</option>
                                @foreach($routers as $router)
                                <option value="{{ $router->id }}">{{ $router->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        @endif

                        <!-- Filter Paket -->
                        <div class="relative w-full sm:w-44">
                            <select wire:model.live="filterPackage" class="w-full pl-3 pr-10 py-2 text-sm border border-gray-300 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none bg-white dark:bg-slate-900 dark:text-slate-300 transition-colors">
                                <option value="">Semua Paket</option>
                                @foreach($allPackages as $pkg)
                                <option value="{{ $pkg->id }}">{{ $pkg->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        @php
                        $hasStatic = auth()->user()->hasFeature('static');
                        $hasPppoe = auth()->user()->hasFeature('pppoe');
                        @endphp
                        @if($hasStatic || $hasPppoe)
                        <!-- Filter Layanan -->
                        <div class="relative w-full sm:w-40">
                            <select wire:model.live="filterService" class="w-full pl-3 pr-10 py-2 text-sm border border-gray-300 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none bg-white dark:bg-slate-900 dark:text-slate-300 transition-colors">
                                <option value="">Semua Layanan</option>
                                @if($hasStatic) <option value="static">STATIC</option> @endif
                                @if($hasPppoe) <option value="pppoe">PPPoE</option> @endif
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
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
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        <!-- Filter Jatuh Tempo -->
                        <div class="relative w-full sm:w-40">
                            <select wire:model.live="filterDueDate" class="w-full pl-3 pr-10 py-2 text-sm border border-gray-300 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none bg-white dark:bg-slate-900 dark:text-slate-300 transition-colors">
                                <option value="">Semua Tanggal</option>
                                @for($i = 1; $i <= 31; $i++)
                                    <option value="{{ $i }}">Tanggal {{ $i }}</option>
                                    @endfor
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        <!-- Search -->
                        <div class="relative w-full sm:w-64">
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari Nama / ID Pelanggan..."
                                class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm bg-white dark:bg-slate-900 dark:text-slate-300 transition-colors">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
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
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        {{ $customer_id ? 'Detail Pelanggan' : 'Registrasi Pelanggan Baru' }}
                                    </h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-wider font-semibold">Manajemen Konektivitas & Billing</p>
                                </div>
                                <button wire:click="closeModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-500 hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-900/30 transition-all duration-300">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Modal Body -->
                            <form wire:submit.prevent="store">
                                <div class="px-8 py-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                        <div>
                                            <div class="flex items-center justify-between mb-2">
                                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">ID Pelanggan</label>
                                                @if($unique_code)
                                                <span class="text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 px-2 py-0.5 rounded border border-amber-200 dark:border-amber-800" title="Kode Unik Billing">Code: {{ str_pad($unique_code, 3, '0', STR_PAD_LEFT) }}</span>
                                                @endif
                                            </div>
                                            <div class="relative flex gap-2">
                                                <input type="text" wire:model="id_pelanggan" placeholder="EB-XXXX"
                                                    class="flex-1 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors border-l-4 border-l-blue-500">
                                                <button type="button" wire:click="generateIdPelanggan" class="px-3 bg-slate-100 dark:bg-slate-700 text-slate-500 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                    </svg>
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

                                        <!-- No HP 1 -->
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">WhatsApp 1</label>
                                            <input type="text" wire:model="phone" placeholder="08..."
                                                oninput="
                                                        let original = this.value;
                                                        let val = original.replace(/\D/g, '');
                                                        if (val.startsWith('62')) {
                                                            val = '0' + val.substring(2);
                                                        }
                                                        if (original !== val) {
                                                            this.value = val;
                                                            this.dispatchEvent(new Event('input'));
                                                        }
                                                    "
                                                class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors">
                                            @error('phone') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                                        </div>

                                        <!-- No HP 2 -->
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">WhatsApp 2 (Opsional)</label>
                                            <input type="text" wire:model="phone2" placeholder="08..."
                                                oninput="
                                                        let original = this.value;
                                                        let val = original.replace(/\D/g, '');
                                                        if (val.startsWith('62')) {
                                                            val = '0' + val.substring(2);
                                                        }
                                                        if (original !== val) {
                                                            this.value = val;
                                                            this.dispatchEvent(new Event('input'));
                                                        }
                                                    "
                                                class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors">
                                            @error('phone2') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
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

                                        @if(!$customer_id)
                                        <!-- Trial Checkbox -->
                                        <div class="sm:col-span-2 flex items-center p-4 bg-amber-50/50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-900/30 rounded-2xl gap-3">
                                            <div class="flex items-center h-5">
                                                <input id="is_trial" wire:model="is_trial" type="checkbox" class="w-5 h-5 text-blue-600 bg-white border-slate-300 rounded-lg focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-slate-800 focus:ring-2 dark:bg-slate-700 dark:border-slate-600 transition-all cursor-pointer">
                                            </div>
                                            <div class="flex-1">
                                                <label for="is_trial" class="font-bold text-slate-700 dark:text-slate-200 text-sm cursor-pointer">Mode Trial (30 Menit)</label>
                                                <p class="text-[10px] text-slate-500 dark:text-slate-400">Jika dicentang, pelanggan akan aktif selama 30 menit sebelum otomatis diisolir jika belum membayar.</p>
                                            </div>
                                        </div>
                                        @endif

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
                                        <!-- Router -->
                                        <div class="sm:col-span-2">
                                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Pilih Router MikroTik <span class="text-red-500">*</span></label>
                                            <select wire:model.live="router_id" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors border-l-4 border-l-blue-500">
                                                @foreach($routers as $router)
                                                <option value="{{ $router->id }}">{{ $router->name }} ({{ $router->host }})</option>
                                                @endforeach
                                            </select>
                                            @error('router_id') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                                        </div>
                                        @endif

                                        <!-- Tipe Layanan -->
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Pilih Layanan</label>
                                            <select wire:model.live="service_type" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-orange-500 outline-none transition-colors border-l-4 border-l-orange-500">
                                                @if(auth()->user()->hasFeature('static'))
                                                <option value="static">STATIC (IP Queue)</option>
                                                @endif
                                                @if(auth()->user()->hasFeature('pppoe'))
                                                <option value="pppoe">PPPOE (User & PW)</option>
                                                @endif
                                            </select>
                                        </div>

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

                                        @if(auth()->user()->hasFeature('mikrotik'))
                                        <!-- Tipe Input -->
                                        <div class="sm:col-span-2">
                                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                                                Metode Pembuatan
                                                <span wire:loading wire:target="creation_method, service_type" class="ml-2 text-blue-500 normal-case tracking-normal inline-flex items-center">
                                                    <svg class="animate-spin w-3 h-3 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    Menarik data...
                                                </span>
                                            </label>
                                            <div class="flex items-center gap-6">
                                                <label class="inline-flex items-center cursor-pointer group">
                                                    <input type="radio" wire:model.live="creation_method" value="buat_baru" class="w-4 h-4 text-blue-600 bg-slate-100 border-slate-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-slate-800 focus:ring-2 dark:bg-slate-700 dark:border-slate-600 transition-all">
                                                    <span class="ml-2 text-sm font-bold text-slate-600 dark:text-slate-300 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Baru</span>
                                                </label>
                                                <label class="inline-flex items-center cursor-pointer group">
                                                    <input type="radio" wire:model.live="creation_method" value="sinkron" class="w-4 h-4 text-blue-600 bg-slate-100 border-slate-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-slate-800 focus:ring-2 dark:bg-slate-700 dark:border-slate-600 transition-all">
                                                    <span class="ml-2 text-sm font-bold text-slate-600 dark:text-slate-300 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">MikroTik (Sinkron)</span>
                                                </label>
                                            </div>
                                        </div>

                                        @if($creation_method === 'sinkron')
                                        <!-- Select Mikrotik Data -->
                                        <div class="sm:col-span-2">
                                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Pilih Sesi/Akun MikroTik yang Aktif</label>
                                            <div class="relative" x-data="{ open: false, search: '' }" @click.away="open = false">
                                                <div @click="open = !open" class="w-full bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/50 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors font-mono cursor-pointer flex justify-between items-center">
                                                    <span x-text="$wire.sync_mikrotik_id ? ($wire.availableMikrotikData.find(d => d.id === $wire.sync_mikrotik_id)?.label || '-- Pilih Data dari Router --') : '-- Pilih Data dari Router --'" class="truncate text-slate-700 dark:text-slate-300"></span>
                                                    <svg class="w-4 h-4 text-slate-400 transform transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                </div>

                                                <div x-show="open" x-transition.opacity x-cloak class="absolute z-50 w-full mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl max-h-60 flex flex-col overflow-hidden" style="display: none;">
                                                    <div class="p-2 border-b border-slate-100 dark:border-slate-700">
                                                        <div class="relative">
                                                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                                </svg>
                                                            </span>
                                                            <input type="text" x-model="search" placeholder="Cari nama / komentar / IP..." class="w-full pl-9 bg-slate-50 dark:bg-slate-900 border-none rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors" @click.stop>
                                                        </div>
                                                    </div>
                                                    <ul class="overflow-y-auto flex-1 p-1">
                                                        <template x-for="item in $wire.availableMikrotikData.filter(i => i.label.toLowerCase().includes(search.toLowerCase()))" :key="item.id">
                                                            <li @click="$wire.set('sync_mikrotik_id', item.id); open = false; search = ''"
                                                                class="px-3 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-blue-900/30 cursor-pointer rounded-lg font-mono truncate transition-colors flex items-center justify-between group">
                                                                <span x-text="item.label" class="truncate"></span>
                                                                <svg x-show="$wire.sync_mikrotik_id === item.id" class="w-4 h-4 text-blue-500 flex-shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                                </svg>
                                                            </li>
                                                        </template>
                                                        <li x-show="$wire.availableMikrotikData.filter(i => i.label.toLowerCase().includes(search.toLowerCase())).length === 0" class="px-4 py-4 text-sm text-slate-400 text-center italic font-medium">Data tidak ditemukan</li>
                                                    </ul>
                                                </div>
                                            </div>
                                            @if(empty($availableMikrotikData))
                                            <p class="text-[10px] text-amber-600 dark:text-amber-400 mt-2 font-bold flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                Sedang memuat data dari router MikroTik atau tidak ada data tersedia untuk layanan ini.
                                            </p>
                                            @else
                                            <p class="text-[10px] text-blue-600 dark:text-blue-400 mt-2 font-bold flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Pilih data untuk mengisi Parameter Layanan secara otomatis.
                                            </p>
                                            @endif
                                        </div>
                                        @endif
                                        @endif


                                        @if(auth()->user()->hasFeature('mikrotik') && $service_type === 'pppoe')
                                        <!-- PPPOE Config -->
                                        <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6 p-5 bg-indigo-50/30 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-900/30 rounded-3xl">
                                            <div class="sm:col-span-2">
                                                <p class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest mb-4">Konfigurasi PPPoE</p>
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
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                                        </svg>
                                                        @else
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
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
                                                <input type="text"
                                                    oninput="
                                                                let val = this.value.toUpperCase().replace(/[^0-9A-F]/g, '');
                                                                let formatted = '';
                                                                for(let i=0; i<val.length && i<12; i++) {
                                                                    if(i > 0 && i % 2 === 0) formatted += ':';
                                                                    formatted += val[i];
                                                                }
                                                                this.value = formatted;
                                                                this.dispatchEvent(new Event('input'));
                                                            "
                                                    wire:model="mac_address" placeholder="00:00:00:00:00:00"
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
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                            </svg>
                                                        </span>
                                                        <svg wire:loading wire:target="autoAssignIp" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                                @error('ip_address') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                                                @if($autoAssignError)
                                                <p class="text-red-500 text-[10px] mt-1.5 font-semibold flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                    </svg>
                                                    {{ $autoAssignError }}
                                                </p>
                                                @endif
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
                                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span>Memproses...</span>
                                        </div>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif

                @if($isSyncModalOpen)
                <div class="fixed z-[60] inset-0 overflow-y-auto">
                    <div class="flex items-center justify-center min-h-screen px-4 py-8">
                        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" wire:click="closeSyncModal()"></div>

                        <div class="relative bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-5xl z-10 border border-slate-200 dark:border-slate-700 overflow-hidden transform transition-all flex flex-col max-h-[90vh]"
                            x-on:click.stop>
                            <!-- Modal Header -->
                            <div class="flex items-center justify-between px-8 py-6 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 flex-shrink-0">
                                <div>
                                    <h3 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                        </div>
                                        Data MikroTik Belum Tersinkron
                                    </h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-wider font-semibold">Berikut adalah daftar layanan di MikroTik yang tidak ada di database pelanggan</p>
                                </div>
                                <button wire:click="closeSyncModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-500 hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-900/30 transition-all duration-300">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Modal Body -->
                            <div class="px-8 py-6 overflow-y-auto custom-scrollbar flex-1" x-data="{ tab: 'static' }">
                                @if($syncError)
                                <div class="bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/30 text-red-600 dark:text-red-400 px-6 py-4 rounded-2xl mb-6 flex items-center gap-3">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="font-semibold text-sm">{{ $syncError }}</span>
                                </div>
                                @endif
                                <div class="flex border-b border-gray-200 dark:border-slate-700 mb-4">
                                    <button @click="tab = 'static'" :class="tab === 'static' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-slate-400 dark:hover:text-slate-300'" class="py-2 px-4 border-b-2 font-medium text-sm transition-colors">
                                        Layanan Static (DHCP Leases)
                                        <span class="ml-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 py-0.5 px-2 rounded-full text-xs">{{ count($unmatchedStatic) }}</span>
                                    </button>
                                    <button @click="tab = 'pppoe'" :class="tab === 'pppoe' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-slate-400 dark:hover:text-slate-300'" class="py-2 px-4 border-b-2 font-medium text-sm transition-colors">
                                        Layanan PPPoE (PPP Active)
                                        <span class="ml-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 py-0.5 px-2 rounded-full text-xs">{{ count($unmatchedPppoe) }}</span>
                                    </button>
                                </div>

                                <!-- Tab Static -->
                                <div x-show="tab === 'static'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                                    @if(count($unmatchedStatic) > 0)
                                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-slate-700 shadow-sm">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                                            <thead class="bg-gray-50 dark:bg-slate-900/50">
                                                <tr>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">IP Address</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">MAC Address</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Server</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Host Name</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Comment</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                                                @foreach($unmatchedStatic as $item)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30">
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-900 dark:text-white">{{ $item['ip_address'] }}</td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-600 dark:text-slate-400">{{ $item['mac_address'] }}</td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">{{ $item['server'] }}</td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">{{ $item['host_name'] }}</td>
                                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-slate-400">{{ $item['comment'] }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <div class="text-center py-10 px-4">
                                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-900/30 mb-4">
                                            <svg class="w-8 h-8 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">Semua Layanan Static Sudah Sinkron</h3>
                                        <p class="text-sm text-gray-500 dark:text-slate-400">Tidak ada DHCP lease di MikroTik yang tidak terhubung dengan database pelanggan lokal.</p>
                                    </div>
                                    @endif
                                </div>

                                <!-- Tab PPPoE -->
                                <div x-show="tab === 'pppoe'" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                                    @if(count($unmatchedPppoe) > 0)
                                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-slate-700 shadow-sm">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                                            <thead class="bg-gray-50 dark:bg-slate-900/50">
                                                <tr>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Username</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">IP Address</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Service</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Uptime</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Caller ID (MAC)</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                                                @foreach($unmatchedPppoe as $item)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30">
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-mono font-medium text-gray-900 dark:text-white">{{ $item['username'] }}</td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-600 dark:text-slate-400">{{ $item['address'] }}</td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">{{ $item['service'] }}</td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">{{ $item['uptime'] }}</td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-500 dark:text-slate-400">{{ $item['caller_id'] }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <div class="text-center py-10 px-4">
                                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-900/30 mb-4">
                                            <svg class="w-8 h-8 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">Semua Layanan PPPoE Sudah Sinkron</h3>
                                        <p class="text-sm text-gray-500 dark:text-slate-400">Tidak ada sesi PPP Active di MikroTik yang tidak terhubung dengan database pelanggan lokal.</p>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Modal Footer -->
                            <div class="px-8 py-5 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3 rounded-b-3xl flex-shrink-0">
                                <button type="button" wire:click="closeSyncModal()"
                                    class="px-5 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 transition-all uppercase tracking-widest">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-slate-700 shadow-sm transition-colors mt-6">
                    <table class="min-w-full min-w-[900px] divide-y divide-gray-200 dark:divide-slate-700">
                        <thead class="bg-slate-50/50 dark:bg-slate-900/40">
                            <tr>
                                <th class="px-4 py-3 text-center text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">NO</th>
                                <th class="px-4 py-3 text-left text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">ID PELANGGAN</th>
                                <th class="px-4 py-3 text-left text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest cursor-pointer hover:bg-slate-200/50 dark:hover:bg-slate-800/50 transition-colors group select-none" wire:click="sortBy('name')">
                                    <div class="flex items-center gap-2">
                                        <span>NAMA</span>
                                        <div class="flex flex-col">
                                            <svg class="w-2.5 h-2.5 {{ $sortField === 'name' && $sortDirection === 'asc' ? 'text-blue-500' : 'text-slate-300 dark:text-slate-600 group-hover:text-slate-400' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                            <svg class="w-2.5 h-2.5 -mt-0.5 {{ $sortField === 'name' && $sortDirection === 'desc' ? 'text-blue-500' : 'text-slate-300 dark:text-slate-600 group-hover:text-slate-400' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-left text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">PAKET</th>
                                <th class="px-4 py-3 text-left text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">IP & MAC</th>
                                <th class="px-4 py-3 text-center text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">TEMPO</th>
                                @if(auth()->user()->hasFeature('mikrotik'))
                                <th class="px-4 py-3 text-center text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">LAYANAN</th>
                                @endif
                                <th class="px-4 py-3 text-center text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">STATUS</th>
                                <th class="px-4 py-3 text-right text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700 transition-colors">
                            @forelse($customers as $idx => $customer)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400 text-center">{{ $customers->firstItem() + $idx }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-mono text-gray-600 dark:text-slate-400 font-semibold">{{ $customer->id_pelanggan ?? '-' }}</span>
                                        @if($customer->unique_code)
                                        <div class="flex items-center gap-1 mt-0.5">
                                            <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800" title="Kode Unik Billing">
                                                Code: {{ str_pad($customer->unique_code, 3, '0', STR_PAD_LEFT) }}
                                            </span>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white p-0">
                                    <a href="{{ route('customers.detail', $customer->id) }}" class="block px-4 py-3 hover:text-blue-600 dark:hover:text-blue-400 transition-colors group" wire:navigate>
                                        <span class="group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $customer->name }}</span>
                                        <div class="text-[10px] text-gray-400 font-normal mt-0.5 space-y-0.5">
                                            @if($customer->phone)
                                            <p>{{ $customer->phone }}</p>
                                            @endif
                                            @if($customer->phone2)
                                            <p>{{ $customer->phone2 }}</p>
                                            @endif
                                        </div>
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
                                    @if($customer->service_type === 'static')
                                    <div class="flex flex-col">
                                        <span>{{ $customer->ip_address ?? '-' }}</span>
                                        @if($customer->mac_address)
                                        <span class="text-[10px] text-gray-400 dark:text-slate-500 mt-0.5">{{ $customer->mac_address }}</span>
                                        @endif
                                    </div>
                                    @else
                                    -
                                    @endif
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
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    <div x-data="{ 
                                        open: false, 
                                        pos: { top: 0, left: 0 },
                                        updatePos() {
                                            let rect = this.$refs.btn.getBoundingClientRect();
                                            this.pos.top = rect.bottom + window.scrollY;
                                            this.pos.left = rect.right - 192 + window.scrollX;
                                        }
                                    }" class="inline-block text-left">
                                        <button x-ref="btn" @click="open = !open; if(open) $nextTick(() => updatePos())"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-700/50 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl transition-all text-xs font-bold group">
                                            <span>Aksi</span>
                                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <template x-teleport="body">
                                            <div x-show="open" @click.away="open = false" x-cloak
                                                :style="`position: absolute; top: ${pos.top}px; left: ${pos.left}px;`"
                                                class="w-48 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 z-[9999] py-2 transition-all">
                                                <a href="{{ route('customers.detail', $customer->id) }}" wire:navigate class="px-4 py-2 text-xs font-bold text-blue-600 dark:text-blue-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    Lihat Detail
                                                </a>
                                                <button wire:click="edit({{ $customer->id }})" @click="open = false" class="w-full text-left px-4 py-2 text-xs font-bold text-amber-600 dark:text-amber-500 hover:bg-slate-50 dark:hover:bg-slate-700/50 flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Edit Data
                                                </button>
                                                <div class="border-t border-slate-50 dark:border-slate-700 my-1"></div>
                                                <button @click="
                                                    open = false;
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
                                                " class="w-full text-left px-4 py-2 text-xs font-bold text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Hapus
                                                </button>
                                            </div>
                                        </template>
                                    </div>
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