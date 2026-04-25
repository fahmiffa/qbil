<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ auth()->user()->hasFeature('mikrotik') ? __('Master Paket Static') : __('Master Paket') }}
        </h2>
    </x-slot>


    <div class="w-full">
        <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-lg border border-gray-100 dark:border-slate-700 transition-colors">
            <div class="p-4 sm:p-6 text-gray-900 dark:text-gray-100">

                    @if (session()->has('message'))
                        <div class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-400 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('message') }}</span>
                        </div>
                    @endif
                    @if (session()->has('error'))
                        <div class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-400 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <div class="flex flex-wrap gap-3 mb-4">
                        <button wire:click="create()" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            {{ auth()->user()->hasFeature('mikrotik') ? 'Tambah Paket Static' : 'Tambah Paket' }}
                        </button>
                    </div>


                    @if($isOpen)
                        <div class="fixed z-50 inset-0 overflow-y-auto">
                            <div class="flex items-center justify-center min-h-screen px-4">
                                <div class="fixed inset-0 bg-black/50" aria-hidden="true" wire:click="closeModal()"></div>
                                <div class="relative bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl w-full max-w-lg z-10 border border-transparent dark:border-slate-700 transition-colors" role="dialog" aria-modal="true">
                                    <form wire:submit.prevent="store">
                                        <div class="px-6 pt-5 pb-4 space-y-4">
                                            <div class="flex items-center justify-between border-b border-gray-100 dark:border-slate-700 pb-3">
                                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                                    @if(auth()->user()->hasFeature('mikrotik'))
                                                        {{ $package_id ? 'Edit Paket Static' : 'Tambah Paket Static' }}
                                                    @else
                                                        {{ $package_id ? 'Edit Paket' : 'Tambah Paket' }}
                                                    @endif
                                                </h3>

                                                <button type="button" wire:click="closeModal()" class="text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300 transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>

                                            <div>
                                                <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Nama Paket:</label>
                                                <input type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Contoh: 10 Mbps Home" wire:model="name">
                                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                            </div>

                                            <div>
                                                <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Harga Bulanan (Rp):</label>
                                                <input type="text" 
                                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" 
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

                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Download Speed:</label>
                                                    <div class="flex">
                                                        <input type="number" class="w-full border border-gray-300 rounded-l-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="10" wire:model="download_value" min="1">
                                                        <select class="border border-l-0 border-gray-300 rounded-r-lg px-2 py-2 text-sm focus:outline-none bg-gray-50 dark:bg-slate-900 dark:text-slate-300" wire:model="download_unit">
                                                            <option value="M">M</option>
                                                            <option value="K">K</option>
                                                        </select>
                                                    </div>
                                                    @error('download_value') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                                </div>
                                                <div>
                                                    <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Upload Speed:</label>
                                                    <div class="flex">
                                                        <input type="number" class="w-full border border-gray-300 rounded-l-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="10" wire:model="upload_value" min="1">
                                                        <select class="border border-l-0 border-gray-300 rounded-r-lg px-2 py-2 text-sm focus:outline-none bg-gray-50 dark:bg-slate-900 dark:text-slate-300" wire:model="upload_unit">
                                                            <option value="M">M</option>
                                                            <option value="K">K</option>
                                                        </select>
                                                    </div>
                                                    @error('upload_value') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                                </div>
                                            </div>

                                        </div>
                                        <div class="bg-gray-50 dark:bg-slate-900/50 px-6 py-3 border-t border-gray-100 dark:border-slate-700 flex flex-row-reverse gap-2">
                                            <button type="submit" class="inline-flex justify-center rounded-lg px-4 py-2 bg-blue-600 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-all">
                                                Simpan
                                            </button>
                                            <button type="button" wire:click="closeModal()" class="inline-flex justify-center rounded-lg border border-gray-300 dark:border-slate-600 px-4 py-2 bg-white dark:bg-slate-800 text-sm font-medium text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-all">
                                                Batal
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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Nama Paket</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Harga</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Speed (DL/UL)</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700 transition-colors">
                                @forelse($packages as $idx => $package)
                                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">{{ $packages->firstItem() + $idx }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">{{ $package->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 dark:text-blue-400 font-bold">
                                        Rp {{ number_format($package->price, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400">
                                            {{ $package->speed_download ?? '-' }} / {{ $package->speed_upload ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center space-x-2">
                                        <button wire:click="edit({{ $package->id }})" wire:loading.attr="disabled" class="inline-flex items-center px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-white rounded-md transition-all shadow-sm disabled:opacity-50">Edit</button>
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
                                            class="inline-flex items-center px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded-md transition-all shadow-sm disabled:bg-rose-400">
                                            <span wire:loading.remove wire:target="delete({{ $package->id }})">Hapus</span>
                                            <span wire:loading wire:target="delete({{ $package->id }})">Menghapus...</span>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-500 dark:text-slate-500 italic">Belum ada data paket.</td>
                                </tr>
                                @endforelse

                            </tbody>
                        </table>
                    </div>
                @if($packages->hasPages())
                    <div class="mt-6 px-4 py-3 bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 sm:px-6 rounded-b-lg shadow-sm text-gray-700 dark:text-slate-400">
                        {{ $packages->links() }}
                    </div>
                @endif
        </div>
    </div>
</div>
