<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Master Asset') }}
        </h2>
    </x-slot>

    <div class="w-full">
        <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-lg transition-colors">
            <div class="p-4 sm:p-6 text-gray-900 dark:text-gray-100">

                <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                    <div class="flex flex-wrap gap-2">
                        <button wire:click="create()" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah Asset
                        </button>
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

                        <!-- Search -->
                        <div class="relative w-full sm:w-64">
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari Nama Asset..." 
                                class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm bg-white dark:bg-slate-900 dark:text-slate-300 transition-colors">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Create / Edit -->
                @if($isOpen)
                    <div class="fixed z-50 inset-0 overflow-y-auto">
                        <div class="flex items-center justify-center min-h-screen px-4 py-8">
                            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" wire:click="closeModal()"></div>
                            
                            <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg z-10 border border-slate-200 dark:border-slate-700 overflow-hidden transform transition-all"
                                 x-on:click.stop>
                                <!-- Modal Header -->
                                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50">
                                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">
                                        {{ $asset_id ? 'Edit Asset' : 'Tambah Asset Baru' }}
                                    </h3>
                                    <button wire:click="closeModal()" class="text-slate-500 hover:text-red-500 transition-colors">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <form wire:submit.prevent="store">
                                    <div class="px-6 py-6 space-y-4 max-h-[70vh] overflow-y-auto custom-scrollbar">
                                        <!-- Nama -->
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Nama Asset <span class="text-red-500">*</span></label>
                                            <input type="text" wire:model="name" placeholder="Misalnya: Modem ZTE, OLT Huawei..."
                                                class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors @error('name') border-red-400 @enderror">
                                            @error('name') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                                        </div>

                                        <!-- Kategori -->
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Kategori Asset</label>
                                            <div class="flex items-center gap-4 mb-3">
                                                <label class="inline-flex items-center cursor-pointer group">
                                                    <input type="radio" wire:model.live="category_mode" value="old" class="form-radio text-blue-600 focus:ring-blue-500 border-slate-300 dark:border-slate-600 dark:bg-slate-900">
                                                    <span class="ml-2 text-xs font-medium text-slate-600 dark:text-slate-400 group-hover:text-blue-500 transition-colors">Pilih Kategori</span>
                                                </label>
                                                <label class="inline-flex items-center cursor-pointer group">
                                                    <input type="radio" wire:model.live="category_mode" value="new" class="form-radio text-blue-600 focus:ring-blue-500 border-slate-300 dark:border-slate-600 dark:bg-slate-900">
                                                    <span class="ml-2 text-xs font-medium text-slate-600 dark:text-slate-400 group-hover:text-blue-500 transition-colors">Buat Baru</span>
                                                </label>
                                            </div>

                                            @if($category_mode === 'old')
                                                <select wire:model="selected_category" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors">
                                                    <option value="">-- Pilih Kategori --</option>
                                                    @foreach($categories as $cat)
                                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                                    @endforeach
                                                </select>
                                                @error('selected_category') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                                            @else
                                                <input type="text" wire:model="new_category" placeholder="Nama Kategori Baru"
                                                    class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors border-l-4 border-l-blue-500">
                                                @error('new_category') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                                            @endif
                                        </div>

                                        <!-- Alamat Lengkap -->
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Alamat / Lokasi Pemasangan</label>
                                            <textarea wire:model="address" rows="3" placeholder="Alamat lengkap lokasi asset..."
                                                class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors @error('address') border-red-400 @enderror"></textarea>
                                            @error('address') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                                        </div>

                                        <!-- Koordinate -->
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Lokasi Koordinat</label>
                                            <div class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 h-64 mb-3 shadow-inner">
                                                <x-google-map lat="latitude" lng="longitude" />
                                            </div>
                                            <div class="grid grid-cols-2 gap-3">
                                                <div class="relative">
                                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[10px] font-bold text-slate-400 uppercase">LAT</span>
                                                    <input type="text" wire:model="latitude" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl pl-10 pr-4 py-2 text-xs focus:ring-2 focus:ring-blue-500 outline-none font-mono">
                                                </div>
                                                <div class="relative">
                                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[10px] font-bold text-slate-400 uppercase">LNG</span>
                                                    <input type="text" wire:model="longitude" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl pl-10 pr-4 py-2 text-xs focus:ring-2 focus:ring-blue-500 outline-none font-mono">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal Footer -->
                                    <div class="px-8 py-5 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3 rounded-b-2xl">
                                        <button type="button" wire:click="closeModal()"
                                            class="px-5 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 transition-all uppercase tracking-widest">
                                            Batal
                                        </button>
                                        <button type="submit"
                                            class="px-8 py-2.5 text-sm font-black text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-[0_8px_20px_-4px_rgba(37,99,235,0.4)] hover:shadow-[0_12px_24px_-6px_rgba(37,99,235,0.5)] transform hover:-translate-y-0.5 transition-all duration-300 min-w-[120px]">
                                            Simpan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-slate-700 shadow-sm transition-colors">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                        <thead class="bg-gray-50 dark:bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest w-24">ID</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Kategori</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Nama Asset</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest w-48">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700 transition-colors">
                            @forelse($assets as $asset)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600 dark:text-slate-400">{{ $asset->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-tight bg-blue-50 text-blue-600 border border-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800">
                                        {{ $asset->category ?? 'Lainnya' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $asset->name }}
                                    @if($asset->latitude && $asset->longitude)
                                        <p class="text-[9px] text-slate-400 mt-0.5 font-mono">{{ $asset->latitude }}, {{ $asset->longitude }}</p>
                                    @endif
                                    @if($asset->address)
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">{{ $asset->address }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                    <button wire:click="edit({{ $asset->id }})" class="inline-flex items-center px-3 py-1.5 bg-yellow-400 hover:bg-yellow-500 text-white rounded-md transition-all shadow-sm text-xs font-semibold">
                                        Edit
                                    </button>
                                    <button wire:click="delete({{ $asset->id }})" wire:confirm="Yakin ingin menghapus asset ini?" class="inline-flex items-center px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-md transition-all shadow-sm text-xs font-semibold">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-500 dark:text-slate-500 italic">Belum ada data asset.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                    </table>
                </div>

                @if($assets->hasPages() && $perPage !== 'all')
                    <div class="mt-6 px-4 py-3 bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 sm:px-6 rounded-b-lg shadow-sm text-gray-700 dark:text-slate-400">
                        {{ $assets->links() }}
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
