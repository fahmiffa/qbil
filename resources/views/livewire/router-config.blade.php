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
                                        <button type="submit" wire:loading.attr="disabled" wire:target="save" class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-2 px-6 rounded-lg transition-colors">
                                            <span wire:loading.remove wire:target="save">Simpan Perubahan</span>
                                            <span wire:loading wire:target="save">Menyimpan...</span>
                                        </button>
                                        
                                        <button type="button" wire:click="testConnection" wire:loading.attr="disabled" class="bg-slate-600 dark:bg-slate-700 hover:bg-slate-700 dark:hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-2 px-6 rounded-lg transition-colors flex items-center gap-2">
                                            <svg wire:loading wire:target="testConnection" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span wire:loading.remove wire:target="testConnection">Test Koneksi</span>
                                            <span wire:loading wire:target="testConnection">Menghubungkan...</span>
                                        </button>
                                    </div>
                            </form>
                        </div>

                        <div wire:poll.5s class="bg-gray-50 dark:bg-slate-900/50 p-6 rounded-xl border border-gray-200 dark:border-slate-700 transition-colors">
                            <h3 class="text-lg font-semibold mb-4 border-b border-gray-200 dark:border-slate-700 pb-2 text-gray-800 dark:text-white">Status Koneksi</h3>
                            


                            <div class="flex items-center mb-4 gap-3">
                                <div wire:loading.remove wire:target="testConnection">
                                    @if($status_connection == 'online' || $status_connection == 'Connected')
                                        <div class="w-4 h-4 rounded-full bg-green-500 shadow-[0_0_10px_rgba(34,197,94,0.8)]"></div>
                                    @else
                                        <div class="w-4 h-4 rounded-full bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.8)]"></div>
                                    @endif
                                </div>

                                <div wire:loading wire:target="testConnection">
                                    <div class="w-4 h-4 rounded-full bg-blue-500 animate-pulse"></div>
                                </div>

                                <span class="font-bold {{ ($status_connection == 'online' || $status_connection == 'Connected') ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}" wire:loading.remove wire:target="testConnection">
                                    Status: {{ ucfirst($status_connection) }}
                                </span>
                                <span class="font-medium text-blue-600 dark:text-blue-400" wire:loading wire:target="testConnection">Status: Sedang Mengecek...</span>
                            </div>

                            @if($status_connection == 'online' || $status_connection == 'Connected')
                                <div class="mb-4 p-3 bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-800/30 rounded-xl">
                                    <div class="flex justify-between text-xs text-green-700 dark:text-green-400 font-bold mb-1">
                                        <span>Latency (Ping)</span>
                                        <span>{{ $ping_ms ?? '-' }} ms</span>
                                    </div>
                                    <div class="w-full bg-green-200 dark:bg-green-800/30 h-1.5 rounded-full">
                                        <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ min(100, ($ping_ms ?? 0) / 2) }}%"></div>
                                    </div>
                                </div>
                            @endif

                            @if($connection_error)
                                <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-100 dark:border-red-800/50 rounded-xl">
                                    <p class="text-xs font-black text-red-600 dark:text-red-400 uppercase tracking-widest mb-1">Log Error Terakhir</p>
                                    <p class="text-[11px] text-red-500 font-mono leading-relaxed">{{ $connection_error }}</p>
                                </div>
                            @endif

                            <div class="text-[10px] text-gray-500 dark:text-slate-500 italic leading-relaxed">
                                * Sistem melakukan pengecekan koneksi secara otomatis setiap 10 detik.
                                <br>
                                * Pastikan API service di Mikrotik sudah aktif (/ip service set api disabled=no)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
