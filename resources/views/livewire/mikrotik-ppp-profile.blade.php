<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">PPP Profile</h2>
    </x-slot>

    <div class="w-full">
        <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-lg border border-gray-100 dark:border-slate-700 transition-colors">
            <div class="p-4 sm:p-6">

                @if(session('message'))
                    <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('message') }}</span>
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Header -->
                <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white">PPP Profile</h3>
                        <p class="text-sm text-gray-500 dark:text-slate-400">Profil PPPoE langsung dari <code class="bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-300 px-1 rounded">/ppp profile</code></p>
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="loadProfiles()" wire:loading.attr="disabled" wire:target="loadProfiles"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-700 dark:text-slate-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-all shadow-sm">
                            <svg class="w-4 h-4" wire:loading.class="animate-spin" wire:target="loadProfiles" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Refresh
                        </button>
                        <button wire:click="openCreate()" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 px-4 rounded-lg transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
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
                            <div class="absolute inset-0 border-4 border-blue-100 rounded-full"></div>
                            <div class="absolute inset-0 border-4 border-blue-500 rounded-full border-t-transparent animate-spin"></div>
                        </div>
                        <span class="mt-4 font-medium text-slate-500">Mengambil data PPP Profile...</span>
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
                                            <code class="px-2 py-1 bg-blue-50 dark:bg-blue-900/30 rounded text-blue-700 dark:text-blue-400 text-xs font-mono font-semibold">{{ $p['mikrotik_profile'] ?? '-' }}</code>
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
                                            <button wire:click="delete('{{ $p['id'] }}')" wire:confirm="Yakin hapus paket '{{ $p['name'] }}'?"
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all">Hapus</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-6 py-16 text-center text-gray-400 dark:text-slate-500 italic">Tidak ada Daftar Paket ditemukan.</td></tr>
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
                                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">{{ $isEditing ? 'Edit PPP Profile' : 'Tambah PPP Profile' }}</h3>
                                        <button wire:click="closeModal()" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                    </div>
                                </div>
                                <form wire:submit.prevent="save">
                                    <div class="px-6 py-4 space-y-4">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Nama Paket</label>
                                            <input type="text" wire:model="name" placeholder="Contoh: Paket Bronze 10Mbps" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Pilih MikroTik Profile</label>
                                            <select wire:model="mikrotik_profile" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                                <option value="">-- Pilih Profile dari MikroTik --</option>
                                                @foreach($mikrotik_profiles_list as $mp)
                                                    <option value="{{ $mp }}">{{ $mp }}</option>
                                                @endforeach
                                            </select>
                                            @error('mikrotik_profile') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Harga (Rp)</label>
                                            <div x-data="{
                                                rawPrice: @entangle('price'),
                                                displayPrice: '',
                                                format(val) {
                                                    if (!val) return '';
                                                    return new Intl.NumberFormat('id-ID').format(String(val).replace(/[^0-9]/g, ''));
                                                }
                                            }" x-init="displayPrice = format(rawPrice); $watch('rawPrice', val => displayPrice = format(val))">
                                                <input type="text" x-model="displayPrice" 
                                                    @input="
                                                        let raw = $event.target.value.replace(/[^0-9]/g, '');
                                                        rawPrice = raw;
                                                        displayPrice = format(raw);
                                                    "
                                                    placeholder="Contoh: 150.000" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                            </div>
                                            @error('price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                    <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-700 flex gap-3">
                                        <button type="button" wire:click="closeModal()" class="flex-1 px-4 py-3 text-sm font-bold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-all">Batal</button>
                                        <button type="submit" class="flex-1 px-4 py-3 text-sm font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-600/30 transition-all">{{ $isEditing ? 'Simpan' : 'Buat Profile' }}</button>
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
