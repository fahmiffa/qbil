<div
    class="min-h-screen"
    x-data="{}"
    wire:poll.30s="fetchData"
>
    {{-- Header Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">

        {{-- Total ONU --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm">
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Total ONU</p>
            <p class="text-3xl font-black text-slate-800 dark:text-white">{{ count($onuList) }}</p>
        </div>

        {{-- Online --}}
        <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl p-5 border border-emerald-100 dark:border-emerald-800/30 shadow-sm">
            <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-1">Online</p>
            <p class="text-3xl font-black text-emerald-700 dark:text-emerald-300">{{ $totalOnline }}</p>
        </div>

        {{-- Offline --}}
        <div class="bg-rose-50 dark:bg-rose-900/20 rounded-2xl p-5 border border-rose-100 dark:border-rose-800/30 shadow-sm">
            <p class="text-[10px] font-black uppercase tracking-widest text-rose-600 dark:text-rose-400 mb-1">Offline</p>
            <p class="text-3xl font-black text-rose-700 dark:text-rose-300">{{ $totalOffline }}</p>
        </div>

        {{-- Last Updated --}}
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-2xl p-5 border border-blue-100 dark:border-blue-800/30 shadow-sm flex flex-col justify-between">
            <p class="text-[10px] font-black uppercase tracking-widest text-blue-500 dark:text-blue-400 mb-1">Update Terakhir</p>
            <p class="text-sm font-black text-blue-700 dark:text-blue-300">{{ $lastUpdated ?? '-' }}</p>
            <button wire:click="fetchData" wire:loading.attr="disabled"
                class="mt-2 text-[9px] font-black uppercase tracking-widest text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                <svg wire:loading wire:target="fetchData" class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span wire:loading.remove wire:target="fetchData">↺ Refresh</span>
                <span wire:loading wire:target="fetchData">Memuat...</span>
            </button>
        </div>
    </div>

    {{-- Error State --}}
    @if($error)
    <div class="mb-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800/30 rounded-2xl p-5 flex items-start gap-3">
        <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="text-sm font-black text-rose-700 dark:text-rose-300">Gagal Mengambil Data SNMP</p>
            <p class="text-xs text-rose-500 dark:text-rose-400 mt-1">{{ $error }}</p>
        </div>
    </div>
    @endif

    {{-- Toolbar --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm mb-4">
        <div class="px-5 py-4 flex flex-col sm:flex-row gap-3 items-start sm:items-center">
            {{-- Search --}}
            <div class="relative flex-1 max-w-sm">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="searchQuery" type="text"
                    placeholder="Cari serial / deskripsi ONU..."
                    class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            {{-- Status Filter --}}
            <div class="flex gap-1">
                @foreach(['all' => 'Semua', 'online' => 'Online', 'offline' => 'Offline'] as $val => $label)
                <button wire:click="$set('filterStatus', '{{ $val }}')"
                    class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all
                    {{ $filterStatus === $val
                        ? ($val === 'online' ? 'bg-emerald-600 text-white' : ($val === 'offline' ? 'bg-rose-500 text-white' : 'bg-blue-600 text-white'))
                        : 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                    {{ $label }}
                </button>
                @endforeach
            </div>
            {{-- IP info --}}
            <p class="text-[10px] text-slate-400 font-mono ml-auto">OLT: {{ $oltIp }} · SNMP v2c</p>
        </div>
    </div>

    {{-- ONU Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-900/50 text-left">
                        <th class="px-5 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">#</th>
                        <th class="px-5 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="px-5 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Serial Number</th>
                        <th class="px-5 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Deskripsi</th>
                        <th class="px-5 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Port</th>
                        <th class="px-5 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Rx Power</th>
                        <th class="px-5 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tx Power</th>
                        <th class="px-5 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Distance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-700/50">
                    @forelse($filteredOnus as $i => $onu)
                    @if(isset($onu['raw_output']))
                        {{-- Raw output mode (sebelum MIB terpetakan) --}}
                        <tr>
                            <td colspan="8" class="px-5 py-6">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Raw SNMP Output (MIB belum terpetakan):</p>
                                <pre class="text-[11px] text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-900 rounded-xl p-4 overflow-x-auto max-h-96 overflow-y-auto font-mono leading-relaxed">{{ $onu['raw_output'] }}</pre>
                            </td>
                        </tr>
                    @else
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition-colors">
                        <td class="px-5 py-4 text-xs font-mono text-slate-400">{{ $onu['id'] ?? ($i + 1) }}</td>
                        <td class="px-5 py-4">
                            @php $status = $onu['status'] ?? null; @endphp
                            @if($status === '1')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>Online
                                </span>
                            @elseif($status === '2')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Offline
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400">
                                    Unknown
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-xs font-mono font-bold text-slate-800 dark:text-white">{{ $onu['serial_number'] ?? '-' }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-xs text-slate-600 dark:text-slate-400 max-w-[180px] truncate" title="{{ $onu['description'] ?? '' }}">
                                {{ $onu['description'] ?? '-' }}
                            </p>
                        </td>
                        <td class="px-5 py-4 text-xs text-slate-600 dark:text-slate-400">{{ $onu['port'] ?? '-' }}</td>
                        <td class="px-5 py-4">
                            @php $rx = $onu['rx_power'] ?? null; @endphp
                            @if($rx !== null)
                                <span class="text-xs font-mono font-bold {{ (float)$rx < -27 ? 'text-rose-500' : 'text-emerald-600 dark:text-emerald-400' }}">
                                    {{ $rx }} dBm
                                </span>
                            @else
                                <span class="text-xs text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @php $tx = $onu['tx_power'] ?? null; @endphp
                            <span class="text-xs font-mono text-slate-600 dark:text-slate-400">{{ $tx !== null ? $tx . ' dBm' : '-' }}</span>
                        </td>
                        <td class="px-5 py-4 text-xs text-slate-600 dark:text-slate-400">
                            {{ isset($onu['distance']) ? $onu['distance'] . ' m' : '-' }}
                        </td>
                    </tr>
                    @endif
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center">
                            @if($isLoading)
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="animate-spin w-8 h-8 text-blue-500" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    <p class="text-sm font-bold text-slate-400">Mengambil data dari OLT...</p>
                                </div>
                            @else
                                <p class="text-sm text-slate-400 italic">Tidak ada ONU ditemukan.</p>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Auto-refresh note --}}
    <p class="text-center text-[10px] text-slate-400 mt-4 font-bold uppercase tracking-widest">
        Data diperbarui otomatis setiap 30 detik · SNMP OID 1.3.6.1.4.1.25355
    </p>
</div>
