<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Hotspot') }}
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

                    <button wire:click="create()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg mb-4 transition-colors">
                        Tambah User Hotspot
                    </button>

                    @if($isOpen)
                        <div class="fixed z-50 inset-0 overflow-y-auto">
                            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                <div class="fixed inset-0 transition-opacity bg-black/50" aria-hidden="true"></div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" role="dialog" aria-modal="true">
                                    <form wire:submit.prevent="store">
                                        <div class="bg-white dark:bg-slate-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 space-y-4">
                                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $hotspot_user_id ? 'Edit User Hotspot' : 'Tambah User Hotspot' }}</h3>
                                            <div>
                                                <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Username:</label>
                                                <input type="text" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors" wire:model="username">
                                                @error('username') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                            </div>
                                            <div>
                                                <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Password:</label>
                                                <x-password-input id="password" wire:model="password" />
                                                @error('password') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                            </div>
                                            <div>
                                                <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Paket/Profile:</label>
                                                <select class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors" wire:model.live="package_id">
                                                    <option value="">Pilih Paket...</option>
                                                    @foreach($packages_list as $p)
                                                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->mikrotik_profile }})</option>
                                                    @endforeach
                                                </select>
                                                @error('package_id') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <div class="bg-gray-50 dark:bg-slate-900/50 px-4 py-3 sm:px-6 flex flex-row-reverse gap-2">
                                            <button type="submit" class="inline-flex justify-center rounded-md border border-transparent px-4 py-2 bg-blue-600 text-sm font-medium text-white shadow-sm hover:bg-blue-700 transition-colors">
                                                Simpan
                                            </button>
                                            <button wire:click="closeModal()" type="button" class="inline-flex justify-center rounded-md border border-gray-300 dark:border-slate-600 px-4 py-2 bg-white dark:bg-slate-800 text-sm font-medium text-gray-700 dark:text-slate-300 shadow-sm hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                                                Batal
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-slate-700 shadow-sm transition-colors">
                        <table class="min-w-full min-w-[850px] divide-y divide-gray-200 dark:divide-slate-700">
                            <thead class="bg-gray-50 dark:bg-slate-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Username</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Password</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Paket</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700 transition-colors">
                                @forelse($hotspotUsers as $idx => $hu)
                                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400 text-center">{{ $hotspotUsers->firstItem() + $idx }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $hu->username }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400 font-mono">{{ $hu->password }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                        <div class="font-bold text-slate-800 dark:text-white">{{ $hu->package->name ?? '-' }}</div>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400 uppercase">
                                            {{ $hu->profile }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center space-x-2">
                                        <button wire:click="edit({{ $hu->id }})" class="inline-flex items-center px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-white rounded-md transition-all shadow-sm">
                                            Edit
                                        </button>
                                        <button wire:click="delete({{ $hu->id }})" wire:confirm="Yakin ingin menghapus user ini?" class="inline-flex items-center px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded-md transition-all shadow-sm text-sm">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-500 dark:text-slate-500 italic">Belum ada data user hotspot.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-4 px-4 pb-4">
                            {{ $hotspotUsers->links() }}
                        </div>
                    </div>
        </div>
    </div>
</div>
