<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Perangkat OLT') }}
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

                <div class="flex justify-between items-center mb-4">
                    <button wire:click="create()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                        Tambah OLT
                    </button>
                    <div class="w-1/3">
                        <input type="text" wire:model.live="search" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Cari Nama atau IP...">
                    </div>
                </div>

                @if($isOpen)
                <div class="fixed z-50 inset-0 overflow-y-auto">
                    <div class="flex items-center justify-center min-h-screen px-4">
                        <div class="fixed inset-0 bg-black/50" aria-hidden="true"></div>
                        <div class="relative bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all w-full max-w-lg z-10 border border-transparent dark:border-slate-700" role="dialog" aria-modal="true">
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-slate-700">
                                <h3 class="text-lg font-bold text-gray-800 dark:text-white">{{ $olt_id ? 'Edit OLT' : 'Tambah OLT' }}</h3>
                                <button wire:click="closeModal()" class="text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <form>
                                <div class="bg-white dark:bg-slate-800 px-6 py-5 space-y-4 transition-colors">
                                    <div>
                                        <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Nama Perangkat:</label>
                                        <input type="text" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Misal: OLT Huawei MA5608T" wire:model="name">
                                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">IP Address:</label>
                                        <input type="text" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Misal: 192.168.1.100" wire:model="ip">
                                        @error('ip') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Username:</label>
                                        <input type="text" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Username OLT" wire:model="username">
                                        @error('username') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Password:</label>
                                        <input type="password" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Password OLT" wire:model="password">
                                        @error('password') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="bg-gray-50 dark:bg-slate-900/50 px-6 py-3 border-t border-gray-100 dark:border-slate-700 flex flex-row-reverse gap-2">
                                    <button wire:click.prevent="store()" type="button" class="inline-flex justify-center rounded-lg px-4 py-2 bg-blue-600 text-sm font-medium text-white shadow-sm hover:bg-blue-700 transition-colors">
                                        Simpan
                                    </button>
                                    <button wire:click="closeModal()" type="button" class="inline-flex justify-center rounded-lg border border-gray-300 dark:border-slate-600 px-4 py-2 bg-white dark:bg-slate-800 text-sm font-medium text-gray-700 dark:text-slate-300 shadow-sm hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Nama Perangkat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">IP Address</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Username</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700 transition-colors">
                            @forelse($olts as $idx => $olt)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400 text-center">{{ $olts->firstItem() + $idx }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">{{ $olt->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">{{ $olt->ip }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">{{ $olt->username }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center space-x-2">
                                    <button wire:click="edit({{ $olt->id }})" class="inline-flex items-center px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-white rounded-md transition-all shadow-sm">Edit</button>
                                    <button wire:click="delete({{ $olt->id }})" wire:confirm="Yakin ingin menghapus perangkat ini?" class="inline-flex items-center px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded-md transition-all shadow-sm text-sm">Hapus</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500 dark:text-slate-500 italic">Belum ada data perangkat OLT.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $olts->links() }}
                </div>
            </div>
        </div>
    </div>