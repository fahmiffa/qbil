<div wire:init="loadProfiles">

    <div class="w-full">
        <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-lg border border-gray-100 dark:border-slate-700 transition-colors">
            <div class="p-4 sm:p-6">

                @if(session('message'))
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('message') }}</span>
                </div>
                @endif
                @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
                @endif

                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white">Hotspot User Profile</h3>
                        <p class="text-sm text-gray-500 dark:text-slate-400">Profil Hotspot langsung dari <code class="bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-1 rounded">/ip hotspot user profile</code></p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <select wire:model.live="router_id" class="w-full sm:w-auto bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-orange-500 outline-none transition-colors font-semibold text-slate-700 dark:text-slate-300">
                            @foreach($routers as $router)
                            <option value="{{ $router->id }}">{{ $router->name }} ({{ $router->host }})</option>
                            @endforeach
                        </select>
                        <button wire:click="openCreate()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold py-2 px-4 rounded-lg transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Profile
                        </button>
                    </div>
                </div>

                <!-- Error -->
                @if($error)
                <div class="bg-red-50 border border-red-100 text-red-700 px-6 py-8 rounded-2xl text-center">
                    <p class="font-bold text-lg">Koneksi Gagal</p>
                    <p class="text-sm mt-2 opacity-80">{{ $error }}</p>
                    <button wire:click="loadProfiles()" class="mt-4 text-blue-600 font-semibold underline">Coba Lagi</button>
                </div>

                <!-- Loading -->
                @elseif($loading)
                <div class="flex flex-col justify-center items-center py-20">
                    <div class="relative w-12 h-12">
                        <div class="absolute inset-0 border-4 border-orange-100 rounded-full"></div>
                        <div class="absolute inset-0 border-4 border-orange-500 rounded-full border-t-transparent animate-spin"></div>
                    </div>
                    <span class="mt-4 font-medium text-slate-500">Mengambil data Hotspot Profile...</span>
                </div>

                <!-- Table -->
                @else
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-slate-700 shadow-sm transition-colors">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                        <thead class="bg-gray-50 dark:bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Nama Paket</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Profile MikroTik</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Kecepatan (Up/Down)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Harga</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700 transition-colors">
                            @forelse($profiles as $idx => $p)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-slate-400">{{ $idx + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">{{ $p['name'] ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <code class="px-2 py-1 bg-orange-50 dark:bg-orange-900/30 rounded text-orange-700 dark:text-orange-400 text-xs font-mono font-semibold">{{ $p['mikrotik_profile'] ?? '-' }}</code>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-slate-400">
                                    @if(!empty($p['speed_upload']) && !empty($p['speed_download']))
                                    <span class="text-xs bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 px-2 py-1 rounded-md">{{ $p['speed_upload'] }}</span>
                                    <span class="text-slate-400 mx-1">/</span>
                                    <span class="text-xs bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 px-2 py-1 rounded-md">{{ $p['speed_download'] }}</span>
                                    @else
                                    <span class="text-gray-400 dark:text-slate-500 italic text-xs">unlimited / default</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                                    Rp {{ number_format($p['price'] ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right space-x-1">
                                    <button wire:click="openEdit('{{ $p['id'] }}')" class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all">Edit</button>
                                    <button type="button"
                                        @click="Swal.fire({
                                                     title: 'Yakin ingin menghapus?',
                                                     text: 'Profil Hotspot \'{{ $p['name'] }}\' akan dihapus dari sistem dan MikroTik!',
                                                     icon: 'warning',
                                                     showCancelButton: true,
                                                     confirmButtonColor: '#ef4444',
                                                     cancelButtonColor: '#64748b',
                                                     confirmButtonText: 'Ya, Hapus!',
                                                     cancelButtonText: 'Batal',
                                                     reverseButtons: true
                                                 }).then((result) => {
                                                     if (result.isConfirmed) {
                                                         @this.delete('{{ $p['id'] }}')
                                                     }
                                                 })"
                                        wire:loading.attr="disabled" wire:target="delete('{{ $p['id'] }}')"
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all disabled:opacity-50">
                                        <span wire:loading.remove wire:target="delete('{{ $p['id'] }}')">Hapus</span>
                                        <span wire:loading wire:target="delete('{{ $p['id'] }}')">Menghapus...</span>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-gray-400 dark:text-slate-500 italic">Tidak ada Daftar Paket ditemukan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @endif

                <!-- Modal -->
                @if($showModal)
                <div class="fixed z-50 inset-0 overflow-y-auto">
                    <div class="flex items-center justify-center min-h-screen px-4 py-6">
                        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeModal()"></div>
                        <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md z-10 border border-slate-200 dark:border-slate-700 transition-colors">
                            <div class="px-6 pt-6 pb-4 border-b border-slate-100 dark:border-slate-700">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">{{ $isEditing ? 'Edit Hotspot Profile' : 'Tambah Hotspot Profile' }}</h3>
                                    <button wire:click="closeModal()" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg></button>
                                </div>
                            </div>
                            <form wire:submit.prevent="save">
                                @if(session('error'))
                                <div class="px-6 pt-4">
                                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg flex items-center gap-3">
                                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-sm font-semibold">{{ session('error') }}</span>
                                    </div>
                                </div>
                                @endif
                                <div class="px-6 py-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <!-- Mode Selector -->
                                        <div class="col-span-2 flex gap-2 p-1 bg-slate-100 dark:bg-slate-900 rounded-xl mb-4 border border-slate-200 dark:border-slate-700">
                                            <button type="button" wire:click="$set('sync_mode', 'new')"
                                                class="flex-1 py-2 px-4 rounded-lg text-[10px] uppercase tracking-tighter font-bold transition-all {{ $sync_mode === 'new' ? 'bg-white dark:bg-slate-800 text-orange-600 shadow-sm border border-slate-200 dark:border-slate-700' : 'text-slate-500 hover:text-slate-700' }}">
                                                Buat Baru
                                            </button>
                                            <button type="button" wire:click="$set('sync_mode', 'sync')"
                                                class="flex-1 py-2 px-4 rounded-lg text-[10px] uppercase tracking-tighter font-bold transition-all {{ $sync_mode === 'sync' ? 'bg-white dark:bg-slate-800 text-orange-600 shadow-sm border border-slate-200 dark:border-slate-700' : 'text-slate-500 hover:text-slate-700' }}">
                                                Sinkronisasi
                                            </button>
                                        </div>

                                        <div class="col-span-2">
                                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Nama Paket</label>
                                            <input type="text" wire:model="name" placeholder="Contoh: Paket 1 Jam" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-orange-500 outline-none transition-colors">
                                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>

                                        @if($sync_mode === 'sync')
                                        <div class="col-span-2">
                                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Pilih Profile MikroTik</label>
                                            <select wire:model="selected_mikrotik_profile" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-orange-500 outline-none transition-colors">
                                                <option value="">-- Pilih Profile --</option>
                                                @foreach($mikrotik_profiles_list as $mp)
                                                <option value="{{ $mp['name'] }}">{{ $mp['name'] }} ({{ $mp['rate-limit'] ?? 'No Limit' }})</option>
                                                @endforeach
                                            </select>
                                            @error('selected_mikrotik_profile') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            <p class="text-[10px] text-slate-400 mt-2 italic px-1">Setting akan disesuaikan.</p>
                                        </div>

                                        <div class="col-span-2">
                                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Limit Time</label>
                                            <input type="text" wire:model="limit_time" placeholder="Contoh: 30d atau 02:00:00"
                                                class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-orange-500 outline-none transition-colors">
                                            @error('limit_time') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            <p class="text-[10px] text-slate-400 mt-1 italic px-1">Gunakan format MikroTik (contoh: 30d, 1w)</p>
                                        </div>
                                        @else
                                        <div class="col-span-2">
                                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Address Pool</label>
                                            <select wire:model="address_pool" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-orange-500 outline-none transition-colors">
                                                <option value="none">none</option>
                                                @foreach($ip_pools as $p)
                                                <option value="{{ $p['name'] }}">{{ $p['name'] }}</option>
                                                @endforeach
                                            </select>
                                            @error('address_pool') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>

                                        <div class="col-span-2">
                                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Limit Time</label>
                                            <input type="text" wire:model="limit_time" placeholder="Contoh: 30d atau 3d 06:00:00"
                                                class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-orange-500 outline-none transition-colors">
                                            @error('limit_time') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            <p class="text-[10px] text-slate-400 mt-1 italic px-1">Gunakan format MikroTik (contoh: 30d, 1w 3d, atau 02:00:00)</p>
                                        </div>

                                        <div class="col-span-1">
                                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Download Speed</label>
                                            <div class="flex">
                                                <input type="number" wire:model="download_value" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-l-xl px-4 py-3 text-sm focus:ring-2 focus:ring-orange-500 outline-none" placeholder="10">
                                                <select wire:model="download_unit" class="bg-slate-100 dark:bg-slate-800 border border-l-0 border-slate-200 dark:border-slate-700 rounded-r-xl px-3 py-3 text-sm focus:ring-2 focus:ring-orange-500 outline-none">
                                                    <option value="M">M</option>
                                                    <option value="K">K</option>
                                                </select>
                                            </div>
                                            @error('download_value') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>

                                        <div class="col-span-1">
                                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Upload Speed</label>
                                            <div class="flex">
                                                <input type="number" wire:model="upload_value" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-l-xl px-4 py-3 text-sm focus:ring-2 focus:ring-orange-500 outline-none" placeholder="10">
                                                <select wire:model="upload_unit" class="bg-slate-100 dark:bg-slate-800 border border-l-0 border-slate-200 dark:border-slate-700 rounded-r-xl px-3 py-3 text-sm focus:ring-2 focus:ring-orange-500 outline-none">
                                                    <option value="M">M</option>
                                                    <option value="K">K</option>
                                                </select>
                                            </div>
                                            @error('upload_value') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>
                                        @endif

                                        <div class="col-span-2">
                                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Harga (Rp)</label>
                                            <input type="text"
                                                class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-orange-500 outline-none transition-colors"
                                                placeholder="150.000"
                                                x-data="{
                                                        formatCurrency(val) {
                                                            if (!val) return '';
                                                            let num = val.toString().replace(/\D/g, '');
                                                            return num.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                                        }
                                                    }"
                                                x-on:input="$event.target.value = formatCurrency($event.target.value); $wire.set('price', $event.target.value.replace(/\D/g, ''))"
                                                x-init="$el.value = formatCurrency($wire.get('price') || '')">
                                            @error('price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>

                                        {{-- ===== VALIDITY FIELDS ===== --}}
                                        <div class="col-span-2">
                                            <div class="rounded-xl border border-dashed border-blue-200 dark:border-blue-800 bg-blue-50/50 dark:bg-blue-900/10 p-3">
                                                <p class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest mb-3">⏱ Pengaturan Masa Aktif Voucher</p>
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Masa Berlaku</label>
                                                        <input type="text" wire:model="valid_duration" placeholder="Contoh: 30d, 1w"
                                                            class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none transition-colors">
                                                        <p class="text-[10px] text-slate-400 mt-1">Batas voucher bisa diaktivasi (sejak dibuat)</p>
                                                        @error('valid_duration') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Masa Aktif</label>
                                                        <input type="text" wire:model="masa_aktif" placeholder="Contoh: 1d, 12h"
                                                            class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none transition-colors">
                                                        <p class="text-[10px] text-slate-400 mt-1">Durasi internet setelah login pertama</p>
                                                        @error('masa_aktif') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-700 flex gap-3">
                                    <button type="button" wire:click="closeModal()" class="flex-1 px-4 py-3 text-sm font-bold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-all">Batal</button>
                                    <button type="submit" wire:loading.attr="disabled" wire:target="save"
                                        class="flex-1 px-4 py-3 text-sm font-bold text-white bg-orange-500 rounded-xl hover:bg-orange-600 shadow-lg shadow-orange-500/30 transition-all disabled:bg-orange-400 disabled:shadow-none flex items-center justify-center gap-2">
                                        <svg wire:loading wire:target="save" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>{{ $isEditing ? 'Simpan' : 'Buat Profile' }}</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>