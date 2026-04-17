<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Router') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-2xl border border-gray-100 dark:border-slate-700 transition-colors">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    


                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <form wire:submit.prevent="save" class="space-y-4">
                                <div>
                                    <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Nama Router / Identitas:</label>
                                    <input type="text" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors" wire:model="name" placeholder="Contoh: Router Pusat">
                                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>
                                
                                <div>
                                    <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Host / IP Address:</label>
                                    <input type="text" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors" wire:model="host" placeholder="192.168.1.1 atau domain.com">
                                    @error('host') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                <div>
                                    <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">API Port:</label>
                                    <input type="number" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors" wire:model="port">
                                    <span class="text-xs text-gray-500 dark:text-slate-500">Default: 8728 (API) atau 8729 (API-SSL)</span>
                                    @error('port') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                <div>
                                    <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Username:</label>
                                    <input type="text" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors" wire:model="username">
                                    @error('username') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                <div>
                                    <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Password:</label>
                                    <input type="password" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors" wire:model="password">
                                    @error('password') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                </div>

                                <div class="flex items-center gap-3 pt-2">
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition-colors">
                                        Simpan Perubahan
                                    </button>
                                    <button type="button" wire:click="testConnection" class="bg-slate-600 dark:bg-slate-700 hover:bg-slate-700 dark:hover:bg-slate-600 text-white font-bold py-2 px-6 rounded-lg transition-colors">
                                        Test Koneksi
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="bg-gray-50 dark:bg-slate-900/50 p-6 rounded-xl border border-gray-200 dark:border-slate-700 transition-colors">
                            <h3 class="text-lg font-semibold mb-4 border-b border-gray-200 dark:border-slate-700 pb-2 text-gray-800 dark:text-white">Status Koneksi</h3>
                            


                            <div class="flex items-center mb-4 gap-3">
                                @if($status_connection == 'Connected')
                                    <div class="w-4 h-4 rounded-full bg-green-500 shadow-[0_0_10px_rgba(34,197,94,0.8)]"></div>
                                @elseif($status_connection == 'Error')
                                    <div class="w-4 h-4 rounded-full bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.8)]"></div>
                                @else
                                    <div class="w-4 h-4 rounded-full bg-gray-400"></div>
                                @endif
                                <span class="font-medium text-gray-700 dark:text-slate-300">Status: {{ $status_connection }}</span>
                            </div>

                            <div class="text-sm text-gray-600 dark:text-slate-400 italic">
                                * Pastikan API service di Mikrotik sudah aktif (/ip service set api disabled=no)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
