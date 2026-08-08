<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Monitor ONU') }}
        </h2>
    </x-slot>

    <div class="w-full space-y-4">

        {{-- Top Bar --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                {{-- SSE Live Indicator --}}
                <div id="sse-indicator" class="flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-full px-3 py-1.5 shadow-sm">
                    <span class="relative flex h-2 w-2">
                        <span id="sse-ping" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span id="sse-dot" class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span id="sse-label">Live · Refresh setiap 5 detik</span>
                </div>
                {{-- Last Updated --}}
                <span id="last-updated" class="text-[10px] text-slate-400 dark:text-slate-500 hidden"></span>

                {{-- OLT Selector --}}
                @if($allOlts->isNotEmpty())
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <select id="olt-selector"
                            class="appearance-none pl-3 pr-8 py-1.5 text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500 cursor-pointer transition-all">
                            @foreach($allOlts as $o)
                            <option value="{{ $o->id }}">{{ $o->name }} &mdash; {{ $o->ip }}</option>
                            @endforeach
                        </select>
                        <svg class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    {{-- Selected OLT label badge --}}
                    <span id="olt-selected-label" class="hidden items-center gap-1 px-2.5 py-1 rounded-full bg-teal-50 dark:bg-teal-900/30 border border-teal-200 dark:border-teal-800 text-[11px] font-bold text-teal-700 dark:text-teal-300">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                        <span id="olt-selected-text"></span>
                    </span>
                </div>
                @endif
            </div>
            {{-- Button OLT -> buka modal OLT --}}
            <button onclick="document.getElementById('olt-modal').classList.remove('hidden')"
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                OLT
            </button>
        </div>

        {{-- Stats Summary (populated by SSE) --}}
        <div id="stats-container" class="grid grid-cols-3 gap-4 hidden">
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 p-4 shadow-sm flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                    <svg class="w-5 h-5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>
                </div>
                <div>
                    <p id="stat-total" class="text-2xl font-black text-slate-800 dark:text-white">0</p>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total ONU</p>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 p-4 shadow-sm flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </div>
                <div>
                    <p id="stat-online" class="text-2xl font-black text-emerald-600 dark:text-emerald-400">0</p>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Online</p>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 p-4 shadow-sm flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </div>
                <div>
                    <p id="stat-offline" class="text-2xl font-black text-red-500 dark:text-red-400">0</p>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Offline</p>
                </div>
            </div>
        </div>

        {{-- ONU Live Table --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-teal-50 dark:bg-teal-900/30 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-black text-slate-800 dark:text-white">
                        Daftar ONU <span id="olt-name-badge" class="hidden ml-1 text-[10px] font-semibold bg-teal-100 dark:bg-teal-900/40 text-teal-700 dark:text-teal-300 px-2 py-0.5 rounded-full"></span>
                    </h3>
                </div>
                {{-- Search in table --}}
                <input type="text" id="onu-search" placeholder="Cari nama / MAC / ID PON..."
                    class="border border-gray-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-teal-500 w-60 transition-all">
            </div>

            {{-- Empty State --}}
            <div id="onu-empty" class="py-20 text-center">
                <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.143 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                </svg>
                <p class="text-sm text-slate-400 dark:text-slate-500">Menghubungkan ke OLT...</p>
            </div>

            {{-- Error State --}}
            <div id="onu-error" class="hidden py-16 text-center px-6">
                <svg class="w-12 h-12 text-red-300 dark:text-red-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <p id="onu-error-msg" class="text-sm text-red-400 dark:text-red-500">Gagal terhubung ke OLT.</p>
                <p class="text-xs text-slate-400 mt-1">Pastikan OLT sudah dikonfigurasi dan dapat diakses.</p>
            </div>

            {{-- Stale Data Banner (shown above table when serving cached data) --}}
            <div id="onu-stale-banner" class="hidden px-5 py-2.5 bg-amber-50 dark:bg-amber-900/20 border-b border-amber-200 dark:border-amber-800 flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p id="onu-stale-msg" class="text-[11px] text-amber-700 dark:text-amber-400">Menampilkan data terakhir — OLT lambat merespons, mencoba lagi...</p>
            </div>

            {{-- Table --}}
            <div id="onu-table-wrapper" class="hidden overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-slate-700 text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-900/50">
                        <tr>
                            <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">ID PON</th>
                            <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Nama</th>
                            <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">MAC Address</th>
                            <th class="px-3 py-3 text-center text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Status</th>
                            <th class="px-3 py-3 text-center text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Rx Power</th>
                            <th class="px-3 py-3 text-center text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Tx Power</th>
                        </tr>
                    </thead>
                    <tbody id="onu-tbody" class="bg-white dark:bg-slate-800 divide-y divide-gray-50 dark:divide-slate-700/50">
                    </tbody>
                </table>
                <div class="px-5 py-2 border-t border-slate-100 dark:border-slate-700 text-[10px] text-slate-400 text-right">
                    <span id="onu-count-label">0 ONU ditampilkan</span>
                </div>
            </div>
        </div>

    </div>

    {{-- =====================================================================
         MODAL OLT (list perangkat OLT)
         ===================================================================== --}}
    <div id="olt-modal" class="hidden fixed z-50 inset-0 overflow-y-auto">
        <div class="flex items-start justify-center min-h-screen px-4 pt-16 pb-10">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="document.getElementById('olt-modal').classList.add('hidden')"></div>
            <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-4xl z-10 border border-transparent dark:border-slate-700 overflow-hidden">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-slate-700">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-black text-slate-800 dark:text-white">Kelola Perangkat OLT</h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <button wire:click="create()" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-colors flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            Tambah OLT
                        </button>
                        <button onclick="document.getElementById('olt-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-300 transition-colors p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Search & Flash --}}
                <div class="px-6 py-3 border-b border-gray-50 dark:border-slate-700/50 bg-slate-50/50 dark:bg-slate-900/30">
                    @if (session()->has('message'))
                    <div class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-400 px-4 py-2 rounded-lg text-sm mb-3">
                        {{ session('message') }}
                    </div>
                    @endif
                    <input type="text" wire:model.live="search" class="w-full max-w-xs border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Cari nama atau IP...">
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-slate-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-slate-900/50">
                            <tr>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">No</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Nama Perangkat</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">IP Address</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Username</th>
                                <th class="px-5 py-3 text-center text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-50 dark:divide-slate-700/50">
                            @forelse($olts as $idx => $olt)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-5 py-3 text-slate-500 dark:text-slate-400 text-center">{{ $olts->firstItem() + $idx }}</td>
                                <td class="px-5 py-3 font-bold text-gray-900 dark:text-white">{{ $olt->name }}</td>
                                <td class="px-5 py-3 font-mono text-slate-500 dark:text-slate-400">{{ $olt->ip }}</td>
                                <td class="px-5 py-3 text-slate-500 dark:text-slate-400">{{ $olt->username }}</td>
                                <td class="px-5 py-3 text-center space-x-1">
                                    <button wire:click="edit({{ $olt->id }})" class="inline-flex items-center px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-white rounded-md transition-all shadow-sm text-xs font-bold">Edit</button>
                                    <button wire:click="delete({{ $olt->id }})" wire:confirm="Yakin ingin menghapus perangkat ini?" class="inline-flex items-center px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded-md transition-all shadow-sm text-xs font-bold">Hapus</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-400 dark:text-slate-500 italic text-sm">Belum ada data perangkat OLT.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3 border-t border-gray-100 dark:border-slate-700">
                    {{ $olts->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Add/Edit OLT Form Modal --}}
    @if($isOpen)
    <div class="fixed z-[60] inset-0 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm"></div>
            <div class="relative bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl w-full max-w-lg z-10 border border-transparent dark:border-slate-700">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-slate-700">
                    <h3 class="text-sm font-black text-gray-800 dark:text-white">{{ $olt_id ? 'Edit Perangkat OLT' : 'Tambah Perangkat OLT' }}</h3>
                    <button wire:click="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-300 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form>
                    <div class="bg-white dark:bg-slate-800 px-6 py-5 space-y-4">
                        <div>
                            <label class="block text-gray-700 dark:text-slate-300 text-xs font-bold mb-2 uppercase tracking-wider">Nama Perangkat</label>
                            <input type="text" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Misal: OLT Huawei MA5608T" wire:model="name">
                            @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-slate-300 text-xs font-bold mb-2 uppercase tracking-wider">IP Address</label>
                            <input type="text" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="192.168.1.100" wire:model="ip">
                            @error('ip') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-slate-300 text-xs font-bold mb-2 uppercase tracking-wider">Username</label>
                            <input type="text" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Username OLT" wire:model="username">
                            @error('username') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-slate-300 text-xs font-bold mb-2 uppercase tracking-wider">Password</label>
                            <input type="password" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Password OLT" wire:model="password">
                            @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-slate-900/50 px-6 py-3 border-t border-gray-100 dark:border-slate-700 flex flex-row-reverse gap-2">
                        <button wire:click.prevent="store()" type="button" class="inline-flex justify-center rounded-lg px-4 py-2 bg-blue-600 text-sm font-bold text-white shadow-sm hover:bg-blue-700 transition-colors">
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

    {{-- =====================================================================
         MODAL DETAIL ONU & REBOOT
         ===================================================================== --}}
    <div id="onu-detail-modal" class="hidden fixed z-[70] inset-0 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="closeOnuDetail()"></div>
            <div class="relative bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl w-full max-w-md z-10 border border-transparent dark:border-slate-700">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-slate-700">
                    <h3 class="text-sm font-black text-gray-800 dark:text-white">Detail ONU</h3>
                    <button onclick="closeOnuDetail()" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-300 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Perangkat</p>
                            <p id="detail-name" class="font-bold text-slate-800 dark:text-white mt-1"></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status</p>
                            <div id="detail-status" class="mt-1"></div>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ID PON</p>
                            <p id="detail-idpon" class="font-mono text-sm text-slate-700 dark:text-slate-300 mt-1"></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">MAC Address</p>
                            <p id="detail-mac" class="font-mono text-sm text-slate-700 dark:text-slate-300 mt-1"></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Rx Power</p>
                            <p id="detail-rx" class="font-mono text-sm text-blue-600 dark:text-blue-400 font-bold mt-1"></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tx Power</p>
                            <p id="detail-tx" class="font-mono text-sm text-purple-600 dark:text-purple-400 font-bold mt-1"></p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-slate-900/50 px-6 py-4 border-t border-gray-100 dark:border-slate-700 flex flex-row-reverse gap-3">
                    <button id="btn-reboot-onu" type="button" class="inline-flex justify-center items-center gap-2 rounded-lg px-4 py-2 bg-red-600 text-sm font-bold text-white shadow-sm hover:bg-red-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Reboot ONU
                    </button>
                    <button onclick="closeOnuDetail()" type="button" class="inline-flex justify-center rounded-lg border border-gray-300 dark:border-slate-600 px-4 py-2 bg-white dark:bg-slate-800 text-sm font-medium text-gray-700 dark:text-slate-300 shadow-sm hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- =====================================================================
         SSE Script — subscribe to /olts/onu-stream and update table
         ===================================================================== --}}
    @push('scripts')
    <script>
    (function() {
        'use strict';

        // ---- DOM refs ----
        const emptyEl       = document.getElementById('onu-empty');
        const errorEl       = document.getElementById('onu-error');
        const errorMsgEl    = document.getElementById('onu-error-msg');
        const tableWrapper  = document.getElementById('onu-table-wrapper');
        const tbody         = document.getElementById('onu-tbody');
        const statsEl       = document.getElementById('stats-container');
        const statTotal     = document.getElementById('stat-total');
        const statOnline    = document.getElementById('stat-online');
        const statOffline   = document.getElementById('stat-offline');
        const lastUpdated   = document.getElementById('last-updated');
        const sseDot        = document.getElementById('sse-dot');
        const ssePing       = document.getElementById('sse-ping');
        const sseLabel      = document.getElementById('sse-label');
        const oltBadge      = document.getElementById('olt-name-badge');
        const countLabel    = document.getElementById('onu-count-label');
        const searchInput   = document.getElementById('onu-search');
        const oltSelector   = document.getElementById('olt-selector');      // may be null if no OLTs
        const oltSelLabel   = document.getElementById('olt-selected-label');
        const oltSelText    = document.getElementById('olt-selected-text');
        const staleBanner   = document.getElementById('onu-stale-banner');  // may be null
        const staleMsgEl    = document.getElementById('onu-stale-msg');     // may be null

        let allRows   = [];   // full dataset
        let eventSrc  = null;

        // ---- Selected OLT label sync ----
        function syncOltLabel() {
            if (!oltSelector || !oltSelLabel || !oltSelText) return;
            const opt = oltSelector.options[oltSelector.selectedIndex];
            if (opt) {
                oltSelText.textContent = opt.text;
                oltSelLabel.classList.remove('hidden');
                oltSelLabel.classList.add('inline-flex');
            }
        }

        // ---- SSE connection ----
        function connectSSE() {
            if (eventSrc) {
                eventSrc.close();
            }

            setIndicator('connecting');

            // Reset table state when switching OLT
            allRows  = [];
            tbody.innerHTML = '';
            statsEl.classList.add('hidden');
            showEmpty();

            const baseUrl  = '{{ route("olts.onu-stream") }}';
            const oltId    = oltSelector ? oltSelector.value : '';
            const url      = oltId ? baseUrl + '?olt_id=' + encodeURIComponent(oltId) : baseUrl;
            eventSrc       = new EventSource(url);

            eventSrc.addEventListener('onu-update', function(e) {
                const payload = JSON.parse(e.data);
                handleUpdate(payload);
            });

            eventSrc.addEventListener('close', function() {
                setIndicator('closed');
                eventSrc.close();
                setTimeout(connectSSE, 10000);
            });

            eventSrc.onerror = function() {
                setIndicator('error');
                eventSrc.close();
                setTimeout(connectSSE, 5000);
            };
        }

        // ---- Handle incoming data ----
        function handleUpdate(payload) {
            if (!payload.success) {
                // If we still have previous rows, keep showing them with stale banner
                if (allRows.length > 0) {
                    setIndicator('stale');
                    if (staleBanner) staleBanner.classList.remove('hidden');
                    if (staleMsgEl)  staleMsgEl.textContent = payload.message || 'OLT lambat merespons, menampilkan data terakhir...';
                } else {
                    showError(payload.message || 'Gagal terhubung ke OLT.');
                    setIndicator('error');
                }
                return;
            }

            // Stale cached data
            if (payload.stale) {
                setIndicator('stale');
                if (staleBanner) staleBanner.classList.remove('hidden');
                if (staleMsgEl)  staleMsgEl.textContent = payload.stale_message || 'Menampilkan data terakhir — OLT lambat merespons...';
            } else {
                setIndicator('live');
                if (staleBanner) staleBanner.classList.add('hidden');
            }

            allRows = payload.onuList || [];

            // OLT name badge
            if (payload.oltName && oltBadge) {
                oltBadge.textContent = payload.oltName;
                oltBadge.classList.remove('hidden');
            }

            // Stats
            if (allRows.length > 0 && statsEl) {
                statsEl.classList.remove('hidden');
                if (statTotal)   statTotal.textContent   = payload.stats.total;
                if (statOnline)  statOnline.textContent  = payload.stats.online;
                if (statOffline) statOffline.textContent = payload.stats.offline;
            }

            // Last updated
            if (lastUpdated) {
                const now = new Date();
                lastUpdated.textContent = (payload.stale ? '⏱ Cache: ' : 'Update: ') + now.toLocaleTimeString('id-ID');
                lastUpdated.classList.remove('hidden');
            }

            // Render filtered table
            renderTable(searchInput ? searchInput.value.trim().toLowerCase() : '');
        }

        // ---- Render table rows (with optional filter) ----
        function renderTable(filter) {
            const filtered = filter
                ? allRows.filter(r => {
                    const idPon = (r[0]  || '').toLowerCase();
                    const name  = (r[1]  || '').toLowerCase();
                    const mac   = (r[2]  || '').toLowerCase();
                    return idPon.includes(filter) || name.includes(filter) || mac.includes(filter);
                })
                : allRows;

            if (filtered.length === 0 && allRows.length === 0) {
                showEmpty();
                return;
            }

            showTable();
            countLabel.textContent = filtered.length + ' ONU ditampilkan';

            tbody.innerHTML = filtered.map(row => {
                const isUp    = (row[3] || '') === 'Up';
                const rawName = (row[1] || '').trim();
                // 'NA', '', or missing → show as italic grey placeholder
                const isNoName = !rawName || rawName.toUpperCase() === 'NA';
                const nameCel  = isNoName
                    ? `<span class="italic text-slate-400 dark:text-slate-500 font-normal">Belum bernama</span>`
                    : esc(rawName);

                const rxRaw   = (row[15] || '').trim();
                const txRaw   = (row[14] || '').trim();
                const suhuRaw = (row[11] || '').trim();
                const rxPow   = rxRaw && rxRaw !== '--' && rxRaw !== 'NA' ? rxRaw + ' dBm' : '--';
                const txPow   = txRaw && txRaw !== '--' && txRaw !== 'NA' ? txRaw + ' dBm' : '--';
                const suhu    = suhuRaw && suhuRaw !== '--' && suhuRaw !== 'NA' ? suhuRaw + '°C' : '--';
                const rtt     = (row[10] || '').trim();
                const onSince = (row[16] || '').trim();
                const offLast = (row[17] || '').trim();

                const statusCls = isUp
                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                    : 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400';
                const dotCls  = isUp ? 'bg-emerald-500' : 'bg-red-500';
                const rowOp   = isUp ? '' : 'opacity-60';
                
                // For onclick attributes, escape single quotes
                const idPonSafe = esc(row[0]).replace(/'/g, "\\'");
                const nameSafe = rawName.replace(/'/g, "\\'");
                const macSafe = esc(row[2]).replace(/'/g, "\\'");

                return `<tr class="hover:bg-blue-50 dark:hover:bg-slate-700/50 transition-colors cursor-pointer ${rowOp}"
                            onclick="window.openOnuDetail('${idPonSafe}', '${nameSafe}', '${macSafe}', '${statusCls}', '${dotCls}', '${esc(row[3]) || '--'}', '${rxPow}', '${txPow}')">
                    <td class="px-3 py-2.5 font-mono font-bold text-slate-700 dark:text-slate-300 whitespace-nowrap">${esc(row[0])}</td>
                    <td class="px-3 py-2.5 font-semibold text-slate-800 dark:text-white max-w-[200px] truncate">${nameCel}</td>
                    <td class="px-3 py-2.5 font-mono text-slate-500 dark:text-slate-400 whitespace-nowrap">${esc(row[2])}</td>
                    <td class="px-3 py-2.5 text-center">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold ${statusCls}">
                            <span class="w-1.5 h-1.5 rounded-full ${dotCls}"></span>
                            ${esc(row[3]) || '--'}
                        </span>
                    </td>
                    <td class="px-3 py-2.5 text-center font-mono font-bold text-blue-600 dark:text-blue-400 whitespace-nowrap">${rxPow}</td>
                    <td class="px-3 py-2.5 text-center font-mono font-bold text-purple-600 dark:text-purple-400 whitespace-nowrap">${txPow}</td>
                </tr>`;
            }).join('');
        }

        // ---- Helpers ----
        function showEmpty() {
            emptyEl.classList.remove('hidden');
            errorEl.classList.add('hidden');
            tableWrapper.classList.add('hidden');
        }
        function showError(msg) {
            emptyEl.classList.add('hidden');
            errorEl.classList.remove('hidden');
            tableWrapper.classList.add('hidden');
            errorMsgEl.textContent = msg || 'Gagal terhubung ke OLT.';
        }
        function showTable() {
            emptyEl.classList.add('hidden');
            errorEl.classList.add('hidden');
            tableWrapper.classList.remove('hidden');
        }
        function setIndicator(state) {
            if (state === 'live') {
                sseDot.className  = 'relative inline-flex rounded-full h-2 w-2 bg-emerald-500';
                ssePing.className = 'animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75';
                sseLabel.textContent = 'Live · Refresh setiap 5 detik';
            } else if (state === 'connecting') {
                sseDot.className  = 'relative inline-flex rounded-full h-2 w-2 bg-yellow-400';
                ssePing.className = 'animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-300 opacity-75';
                sseLabel.textContent = 'Menghubungkan...';
            } else if (state === 'stale') {
                sseDot.className  = 'relative inline-flex rounded-full h-2 w-2 bg-amber-400';
                ssePing.className = 'animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-300 opacity-75';
                sseLabel.textContent = 'Data lama · OLT lambat merespons';
            } else {
                sseDot.className  = 'relative inline-flex rounded-full h-2 w-2 bg-red-500';
                ssePing.className = 'animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75';
                sseLabel.textContent = 'Koneksi terputus · Mencoba ulang...';
            }
        }
        
        // ---- Detail Modal & Reboot Logic ----
        window.closeOnuDetail = function() {
            document.getElementById('onu-detail-modal').classList.add('hidden');
        };

        window.openOnuDetail = function(idPon, name, mac, statusCls, dotCls, statusText, rxPow, txPow) {
            document.getElementById('detail-idpon').textContent = idPon;
            document.getElementById('detail-name').textContent = name || 'Belum bernama';
            document.getElementById('detail-mac').textContent = mac;
            document.getElementById('detail-rx').textContent = rxPow;
            document.getElementById('detail-tx').textContent = txPow;
            
            document.getElementById('detail-status').innerHTML = `
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold ${statusCls}">
                    <span class="w-1.5 h-1.5 rounded-full ${dotCls}"></span>
                    ${statusText}
                </span>
            `;

            const btnReboot = document.getElementById('btn-reboot-onu');
            btnReboot.onclick = function() {
                rebootOnu(idPon, name);
            };

            document.getElementById('onu-detail-modal').classList.remove('hidden');
        };

        function rebootOnu(onuId, onuName) {
            const displayName = onuName || onuId;
            Swal.fire({
                title: 'Reboot ONU?',
                text: `Yakin ingin mereboot ONU ${displayName}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Reboot!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                const oltId = oltSelector ? oltSelector.value : '';
                if(!oltId) {
                    Swal.fire('Error', 'Pilih OLT terlebih dahulu.', 'error');
                    return;
                }

                const btnReboot = document.getElementById('btn-reboot-onu');
                btnReboot.disabled = true;
                btnReboot.innerHTML = 'Memproses...';

                fetch('{{ route("olts.reboot-onu") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        olt_id: oltId,
                        onu_id: onuId,
                        onu_name: onuName || ''
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire('Berhasil!', data.message, 'success');
                        window.closeOnuDetail();
                    } else {
                        Swal.fire('Gagal!', data.message, 'error');
                    }
                })
                .catch(err => {
                    Swal.fire('Error!', 'Terjadi kesalahan saat memproses permintaan.', 'error');
                    console.error(err);
                })
                .finally(() => {
                    btnReboot.disabled = false;
                    btnReboot.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Reboot ONU`;
                });
            });
        }
        function esc(val) {
            if (val === undefined || val === null) return '-';
            return String(val)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        // ---- Search filter ----
        searchInput.addEventListener('input', function() {
            if (allRows.length > 0) {
                renderTable(this.value.trim().toLowerCase());
            }
        });

        // ---- OLT selector change → reconnect SSE ----
        if (oltSelector) {
            oltSelector.addEventListener('change', function() {
                syncOltLabel();
                connectSSE();
            });
        }

        // ---- Close SSE on page leave ----
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                if (eventSrc) eventSrc.close();
            } else {
                connectSSE();
            }
        });

        // ---- Start ----
        syncOltLabel();
        connectSSE();
    })();
    </script>
    @endpush

</div>