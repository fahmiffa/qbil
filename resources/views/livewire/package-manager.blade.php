<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Paket') }}
        </h2>
    </x-slot>

    <div class="w-full">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-4 sm:p-6 text-gray-900">
                    
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

                    <div class="flex flex-wrap gap-3 mb-4">
                        <button wire:click="create()" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah Paket
                        </button>
                        <button wire:click="syncFromMikrotik()" wire:loading.attr="disabled" wire:target="syncFromMikrotik"
                            class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg transition-all shadow-sm disabled:opacity-60">
                            <svg class="w-4 h-4" wire:loading.class="animate-spin" wire:target="syncFromMikrotik" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span wire:loading.remove wire:target="syncFromMikrotik">Sinkronisasi dari Router</span>
                            <span wire:loading wire:target="syncFromMikrotik">Menyinkronisasi...</span>
                        </button>
                    </div>

                    @if($isOpen)
                        <div class="fixed z-10 inset-0 overflow-y-auto">
                            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                                </div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-transparent dark:border-slate-700" role="dialog" aria-modal="true" aria-labelledby="modal-headline">
                                    <form>
                                        <div class="bg-white dark:bg-slate-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 space-y-4 transition-colors">
                                                <input type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" id="name" placeholder="Contoh: 10 Mbps" wire:model="name">
                                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                            </div>
                                            <div>
                                                <label for="tipe" class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Tipe Koneksi:</label>
                                                <select class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" id="tipe" wire:model="tipe">
                                                    <option value="PPPOE">PPPOE</option>
                                                    <option value="HOTSPOT">HOTSPOT</option>
                                                    <option value="STATIC">STATIC</option>
                                                </select>
                                                @error('tipe') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                            </div>
                                            <div>
                                                <label for="price" class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Harga Bulanan (Rp):</label>
                                                <input type="text" 
                                                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                                    id="price" 
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
                                                @error('price') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                            </div>
                                            <div class="flex space-x-4">
                                                <div class="w-1/2">
                                                    <label for="speed_download" class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Download Speed:</label>
                                                    <div class="flex">
                                                        <input type="number" class="w-full border border-gray-300 rounded-l-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" id="speed_download" placeholder="10" wire:model="download_value" min="1">
                                                        <select class="border border-l-0 border-gray-300 rounded-r-lg px-2 py-2.5 text-sm focus:outline-none bg-gray-50 dark:bg-slate-900 dark:text-slate-300" wire:model="download_unit">
                                                            <option value="M">M</option>
                                                            <option value="K">K</option>
                                                        </select>
                                                    </div>
                                                    @error('download_value') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                                </div>
                                                <div class="w-1/2">
                                                    <label for="speed_upload" class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Upload Speed:</label>
                                                    <div class="flex">
                                                        <input type="number" class="w-full border border-gray-300 rounded-l-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" id="speed_upload" placeholder="10" wire:model="upload_value" min="1">
                                                        <select class="border border-l-0 border-gray-300 rounded-r-lg px-2 py-2.5 text-sm focus:outline-none bg-gray-50 dark:bg-slate-900 dark:text-slate-300" wire:model="upload_unit">
                                                            <option value="M">M</option>
                                                            <option value="K">K</option>
                                                        </select>
                                                    </div>
                                                    @error('upload_value') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bg-gray-50 dark:bg-slate-900/50 px-4 py-3 sm:px-6 border-t border-gray-100 dark:border-slate-700 flex flex-row-reverse gap-2">
                                            <button wire:click.prevent="store()" type="button" class="inline-flex justify-center rounded-lg px-4 py-2 bg-blue-600 text-sm font-medium text-white shadow-sm hover:bg-blue-700 transition-colors">
                                                Simpan
                                            </button>
                                            <button wire:click="closeModal()" type="button" class="inline-flex justify-center rounded-lg border border-gray-300 dark:border-slate-600 px-4 py-2 bg-white dark:bg-slate-800 text-sm font-medium text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                                                Batal
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-slate-700 shadow-sm transition-colors">
                        <table class="min-w-full min-w-[700px] divide-y divide-gray-200 dark:divide-slate-700">
                            <thead class="bg-gray-50 dark:bg-slate-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Nama Paket</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Tipe</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Harga</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Speed (DL/UL)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Mikrotik Profile</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($packages as $idx => $package)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $packages->firstItem() + $idx }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $package->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="px-2 py-1 rounded text-xs font-bold {{ $package->tipe == 'STATIC' ? 'bg-purple-100 text-purple-800' : ($package->tipe == 'HOTSPOT' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800') }}">
                                            {{ $package->tipe }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-bold">
                                        Rp {{ number_format($package->price, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                            {{ $package->speed_download ?? '-' }} / {{ $package->speed_upload ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $package->mikrotik_profile ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center space-x-2">
                                        <button wire:click="edit({{ $package->id }})" wire:loading.attr="disabled" class="inline-flex items-center px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-white rounded-md transition-all shadow-sm disabled:opacity-50">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            Edit
                                        </button>
                                        <button type="button" 
                                            @click="Swal.fire({
                                                title: 'Yakin ingin menghapus?',
                                                text: 'Paket \'{{ $package->name }}\' akan dihapus permanen!',
                                                icon: 'warning',
                                                showCancelButton: true,
                                                confirmButtonColor: '#ef4444',
                                                cancelButtonColor: '#64748b',
                                                confirmButtonText: 'Ya, Hapus!',
                                                cancelButtonText: 'Batal',
                                                reverseButtons: true
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    @this.delete({{ $package->id }})
                                                }
                                            })"
                                            wire:loading.attr="disabled" wire:target="delete({{ $package->id }})"
                                            class="inline-flex items-center px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded-md transition-all shadow-sm text-sm disabled:bg-rose-400">
                                            <svg wire:loading.remove wire:target="delete({{ $package->id }})" class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span wire:loading.remove wire:target="delete({{ $package->id }})">Hapus</span>
                                            <span wire:loading wire:target="delete({{ $package->id }})">Menghapus...</span>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500 italic">Belum ada data paket internet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
        </div>
    </div>
</div>
