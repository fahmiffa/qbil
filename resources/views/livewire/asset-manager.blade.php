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
                        <div class="flex items-center justify-center min-h-screen px-4">
                            <div class="fixed inset-0 bg-black opacity-40"></div>
                            <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg z-10 border border-transparent dark:border-slate-700 transition-colors">
                                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-slate-700">
                                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">
                                        {{ $asset_id ? 'Edit Asset' : 'Tambah Asset' }}
                                    </h3>
                                    <button wire:click="closeModal()" class="text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300 transition-colors">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <form wire:submit.prevent="store">
                                    <div class="px-6 py-5 bg-white dark:bg-slate-800 transition-colors">
                                        <div>
                                            <label class="block text-gray-700 dark:text-slate-300 text-sm font-semibold mb-1">Nama Asset <span class="text-red-500">*</span></label>
                                            <input type="text" wire:model="name" placeholder="Misalnya: Modem ZTE, OLT Huawei..."
                                                class="w-full border border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-gray-100 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-400 @enderror">
                                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>
                                    </div>

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
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                        <thead class="bg-gray-50 dark:bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest w-24">ID</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Nama Asset</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest w-48">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700 transition-colors">
                            @forelse($assets as $asset)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600 dark:text-slate-400">{{ $asset->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $asset->name }}</td>
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
                                <td colspan="3" class="px-6 py-10 text-center text-gray-500 dark:text-slate-500 italic">Belum ada data asset.</td>
                            </tr>
                            @endforelse
                        </tbody>
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
