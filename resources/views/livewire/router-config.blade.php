<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Router MikroTik') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto space-y-4">

            {{-- Header + Tombol Tambah --}}
            <div class="flex items-center justify-between px-1">
                <div>
                    <p class="text-sm text-gray-500 dark:text-slate-400">Kelola semua router MikroTik yang terhubung ke akun Anda.</p>
                </div>
                @if(!auth()->user()->allow_multi_router && auth()->user()->role != 0 && $routers->count() >= 1)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 text-xs font-semibold border border-orange-200 dark:border-orange-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Limit 1 Router (Upgrade untuk tambah)
                    </span>
                @else
                    <button wire:click="create"
                        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Router
                    </button>
                @endif
            </div>

            {{-- Flash Messages --}}
            @if (session()->has('message'))
                <div class="px-4 py-3 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-xl text-green-700 dark:text-green-300 text-sm">
                    {{ session('message') }}
                </div>
            @endif

            {{-- Tabel Router --}}
            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-2xl border border-gray-100 dark:border-slate-700 overflow-hidden">
                @if($routers->isEmpty())
                    <div class="flex flex-col items-center justify-center py-20 text-gray-400 dark:text-slate-500">
                        <svg class="w-16 h-16 mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
                        </svg>
                        <p class="font-semibold text-base mb-1">Belum ada router terdaftar</p>
                        <p class="text-sm">Klik tombol "Tambah Router" untuk mulai menambahkan router MikroTik.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 dark:bg-slate-900/50 border-b border-gray-100 dark:border-slate-700">
                                <tr>
                                    <th class="px-5 py-3.5 font-semibold text-gray-600 dark:text-slate-300 text-xs uppercase tracking-wider">Router</th>
                                    <th class="px-5 py-3.5 font-semibold text-gray-600 dark:text-slate-300 text-xs uppercase tracking-wider">Host / Port</th>
                                    <th class="px-5 py-3.5 font-semibold text-gray-600 dark:text-slate-300 text-xs uppercase tracking-wider">Status</th>
                                    <th class="px-5 py-3.5 font-semibold text-gray-600 dark:text-slate-300 text-xs uppercase tracking-wider">Pelanggan</th>
                                    <th class="px-5 py-3.5 font-semibold text-gray-600 dark:text-slate-300 text-xs uppercase tracking-wider">Paket</th>
                                    <th class="px-5 py-3.5 font-semibold text-gray-600 dark:text-slate-300 text-xs uppercase tracking-wider">Dicek</th>
                                    <th class="px-5 py-3.5 text-right font-semibold text-gray-600 dark:text-slate-300 text-xs uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-slate-700/50">
                                @foreach($routers as $router)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-700/20 transition-colors">
                                    {{-- Nama --}}
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-800 dark:text-white">
                                            {{ $router->name ?: 'Router #' . $router->id }}
                                        </div>
                                        <div class="text-xs text-gray-400 dark:text-slate-500 font-mono">{{ $router->username }}</div>
                                    </td>
                                    {{-- Host --}}
                                    <td class="px-5 py-4">
                                        <span class="font-mono text-xs text-gray-700 dark:text-slate-300">{{ $router->host }}</span>
                                        <span class="ml-1 text-xs text-gray-400 dark:text-slate-500">:{{ $router->port }}</span>
                                    </td>
                                    {{-- Status --}}
                                    <td class="px-5 py-4">
                                        @if($router->connection_status === 'online')
                                            <div class="inline-flex items-center gap-1.5">
                                                <div class="w-2.5 h-2.5 rounded-full bg-green-500 shadow-[0_0_6px_rgba(34,197,94,0.7)]"></div>
                                                <span class="text-green-600 dark:text-green-400 font-semibold text-xs">Online</span>
                                                @if($router->ping_ms)
                                                    <span class="text-gray-400 dark:text-slate-500 text-xs">{{ $router->ping_ms }}ms</span>
                                                @endif
                                            </div>
                                        @elseif($router->connection_status === 'offline')
                                            <div class="inline-flex items-center gap-1.5">
                                                <div class="w-2.5 h-2.5 rounded-full bg-red-500"></div>
                                                <span class="text-red-500 dark:text-red-400 font-semibold text-xs">Offline</span>
                                            </div>
                                        @else
                                            <div class="inline-flex items-center gap-1.5">
                                                <div class="w-2.5 h-2.5 rounded-full bg-gray-400 dark:bg-slate-500"></div>
                                                <span class="text-gray-400 dark:text-slate-500 text-xs">Belum dicek</span>
                                            </div>
                                        @endif
                                    </td>
                                    {{-- Jumlah Pelanggan --}}
                                    <td class="px-5 py-4">
                                        <span class="font-semibold text-gray-700 dark:text-slate-300">{{ $router->customers_count ?? $router->customers()->count() }}</span>
                                        <span class="text-gray-400 text-xs ml-1">pelanggan</span>
                                    </td>
                                    {{-- Jumlah Paket --}}
                                    <td class="px-5 py-4">
                                        <span class="font-semibold text-gray-700 dark:text-slate-300">{{ $router->packages_count ?? $router->packages()->count() }}</span>
                                        <span class="text-gray-400 text-xs ml-1">paket</span>
                                    </td>
                                    {{-- Terakhir Dicek --}}
                                    <td class="px-5 py-4 text-xs text-gray-400 dark:text-slate-500">
                                        {{ $router->last_checked_at ? $router->last_checked_at->diffForHumans() : '-' }}
                                    </td>
                                    {{-- Aksi --}}
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- Test Koneksi --}}
                                            <button wire:click="testConnection({{ $router->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="testConnection({{ $router->id }})"
                                                class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-blue-50 dark:hover:bg-blue-900/30 text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors disabled:opacity-50">
                                                <svg wire:loading wire:target="testConnection({{ $router->id }})" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                </svg>
                                                <svg wire:loading.remove wire:target="testConnection({{ $router->id }})" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                                </svg>
                                                <span wire:loading.remove wire:target="testConnection({{ $router->id }})">Test</span>
                                                <span wire:loading wire:target="testConnection({{ $router->id }})">Mengecek...</span>
                                            </button>

                                            {{-- Edit --}}
                                            <button wire:click="edit({{ $router->id }})"
                                                class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-amber-50 dark:hover:bg-amber-900/30 text-slate-600 dark:text-slate-300 hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Edit
                                            </button>

                                            {{-- Hapus --}}
                                            <button wire:click="delete({{ $router->id }})"
                                                wire:confirm="Yakin hapus router '{{ $router->name ?: $router->host }}'? Router hanya bisa dihapus jika tidak ada pelanggan atau paket yang menggunakannya."
                                                class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-red-50 dark:hover:bg-red-900/30 text-slate-600 dark:text-slate-300 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Info Tip --}}
            <p class="text-xs text-gray-400 dark:text-slate-500 px-1">
                💡 Setiap pelanggan dan paket dapat dikonfigurasi untuk menggunakan router yang berbeda.
                Router yang belum terhubung tidak akan memproses provisioning pelanggan.
            </p>
        </div>
    </div>

    {{-- Modal Tambah/Edit Router --}}
    @if($isOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-data x-init="$el.querySelector('[data-modal-panel]').focus()">
        {{-- Overlay --}}
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal"></div>

        {{-- Panel --}}
        <div data-modal-panel tabindex="-1"
            class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg p-6 focus:outline-none"
            @keydown.escape.window="$wire.closeModal()">

            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">
                    {{ $router_id_edit ? 'Edit Router' : 'Tambah Router Baru' }}
                </h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-300 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form wire:submit.prevent="save" class="space-y-4">
                {{-- Nama Router --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
                        Nama Router <span class="text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <input type="text" wire:model="name"
                        placeholder="Contoh: Router Pusat, Router Area A"
                        class="w-full border border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Host --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
                        Host / IP Address <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model="host"
                        placeholder="192.168.1.1 atau domain.com"
                        class="w-full border border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                    @error('host') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Port --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
                        API Port <span class="text-red-500">*</span>
                    </label>
                    <input type="number" wire:model="port"
                        class="w-full border border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                    <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Default: 8728 (API) atau 8729 (API-SSL)</p>
                    @error('port') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Username --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
                        Username <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model="username"
                        placeholder="admin"
                        class="w-full border border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                    @error('username') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="{{ $showPassword ? 'text' : 'password' }}" wire:model="password"
                            placeholder="Password API MikroTik"
                            class="w-full border border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 rounded-xl pl-3.5 pr-11 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors font-mono">
                        <button type="button" wire:click="togglePassword"
                            class="absolute inset-y-0 right-0 flex items-center px-3.5 text-gray-400 hover:text-blue-500 transition-colors">
                            @if($showPassword)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            @endif
                        </button>
                    </div>
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Buttons --}}
                <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100 dark:border-slate-700">
                    <button type="button" wire:click="closeModal"
                        class="px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-semibold rounded-xl shadow transition-colors">
                        <svg wire:loading wire:target="save" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="save">
                            {{ $router_id_edit ? 'Simpan Perubahan' : 'Tambah Router' }}
                        </span>
                        <span wire:loading wire:target="save">Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
