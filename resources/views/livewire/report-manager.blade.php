<div class="space-y-6">

    {{-- ═══════════════════════════════════════════════════
         HEADER & FILTER CONTROLS
    ════════════════════════════════════════════════════ --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Filter Laporan</h2>
                <p class="text-sm text-gray-500 dark:text-slate-400">Ringkasan pemasukan berdasarkan periode, metode, dan layanan</p>
            </div>
            <div class="flex gap-2 flex-wrap">
                <button wire:click="setThisMonth" class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-blue-200 dark:border-blue-700 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">Bulan Ini</button>
                <button wire:click="setLastMonth" class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-gray-200 dark:border-slate-600 text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700 transition">Bulan Lalu</button>
                <button wire:click="setThisYear" class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-gray-200 dark:border-slate-600 text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700 transition">Tahun Ini</button>
                <button wire:click="resetFilters" class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-rose-200 dark:border-rose-700 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition">Reset</button>
            </div>
        </div>

        {{-- Filter Row --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Tanggal Mulai --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Dari Tanggal</label>
                <input type="date" wire:model.live="startDate"
                    class="w-full rounded-xl border border-gray-200 dark:border-slate-600 bg-gray-50 dark:bg-slate-700/50 text-gray-800 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            </div>

            {{-- Tanggal Akhir --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Sampai Tanggal</label>
                <input type="date" wire:model.live="endDate"
                    class="w-full rounded-xl border border-gray-200 dark:border-slate-600 bg-gray-50 dark:bg-slate-700/50 text-gray-800 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            </div>

            {{-- Metode Pembayaran --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Metode Pembayaran</label>
                <select wire:model.live="filterPaymentMethod"
                    class="w-full rounded-xl border border-gray-200 dark:border-slate-600 bg-gray-50 dark:bg-slate-700/50 text-gray-800 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    <option value="">Semua Metode</option>
                    @foreach($paymentMethods as $method)
                    <option value="{{ $method->name }}">{{ $method->name }}</option>
                    @endforeach
                    <option value="">— Tidak Ada Metode —</option>
                </select>
            </div>

            {{-- Layanan --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Layanan</label>
                <select wire:model.live="filterServiceType"
                    class="w-full rounded-xl border border-gray-200 dark:border-slate-600 bg-gray-50 dark:bg-slate-700/50 text-gray-800 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    <option value="">Semua Layanan</option>
                    <option value="pppoe">PPPoE</option>
                    <option value="static">Static</option>
                    <option value="hotspot">Hotspot</option>
                </select>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════
         SUMMARY CARDS
    ════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {{-- Total Pemasukan --}}
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-5 shadow-lg shadow-emerald-500/20">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-emerald-100 text-xs font-semibold uppercase tracking-widest mb-1">Total Pemasukan</p>
                    <p class="text-white text-2xl font-extrabold">{{ 'Rp ' . number_format($totalIncome, 0, ',', '.') }}</p>
                    <p class="text-emerald-100 text-xs mt-1">{{ $countIncome }} transaksi</p>
                </div>
                <div class="bg-white/20 rounded-xl p-3">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Total Pengeluaran --}}
        <div class="bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl p-5 shadow-lg shadow-rose-500/20">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-rose-100 text-xs font-semibold uppercase tracking-widest mb-1">Total Pengeluaran</p>
                    <p class="text-white text-2xl font-extrabold">{{ 'Rp ' . number_format($totalExpense, 0, ',', '.') }}</p>
                    <p class="text-rose-100 text-xs mt-1">Pada periode terpilih</p>
                </div>
                <div class="bg-white/20 rounded-xl p-3">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Laba Bersih --}}
        <div class="bg-gradient-to-br {{ $netProfit >= 0 ? 'from-blue-500 to-indigo-600 shadow-blue-500/20' : 'from-amber-500 to-orange-600 shadow-amber-500/20' }} rounded-2xl p-5 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-xs font-semibold uppercase tracking-widest mb-1">Laba Bersih</p>
                    <p class="text-white text-2xl font-extrabold">{{ ($netProfit >= 0 ? '' : '-') . 'Rp ' . number_format(abs($netProfit), 0, ',', '.') }}</p>
                    <p class="text-blue-100 text-xs mt-1">Pemasukan - Pengeluaran</p>
                </div>
                <div class="bg-white/20 rounded-xl p-3">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════
         BREAKDOWN PANELS
    ════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Breakdown Layanan --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-700 dark:text-slate-200 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7" />
                </svg>
                Per Layanan
            </h3>
            @forelse($serviceBreakdown as $row)
            @php
            $percentage = $totalIncome > 0 ? ($row->total / $totalIncome) * 100 : 0;
            $colors = ['pppoe' => 'bg-blue-500', 'static' => 'bg-indigo-500', 'hotspot' => 'bg-violet-500'];
            $color = $colors[strtolower($row->service_type ?? '')] ?? 'bg-gray-400';
            $label = strtoupper($row->service_type ?? 'Lainnya');
            @endphp
            <div class="mb-3">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-xs font-semibold text-gray-600 dark:text-slate-300">{{ $label }}</span>
                    <span class="text-xs text-gray-500 dark:text-slate-400">{{ 'Rp ' . number_format($row->total, 0, ',', '.') }}</span>
                </div>
                <div class="h-2 bg-gray-100 dark:bg-slate-700 rounded-full overflow-hidden">
                    <div class="{{ $color }} h-2 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                </div>
                <p class="text-right text-xs text-gray-400 dark:text-slate-500 mt-0.5">{{ $row->count }} transaksi</p>
            </div>
            @empty
            <p class="text-sm text-gray-400 dark:text-slate-500 text-center py-4">Tidak ada data</p>
            @endforelse
        </div>

        {{-- Breakdown Metode Bayar --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-700 dark:text-slate-200 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Per Metode Bayar
            </h3>
            @forelse($methodBreakdown as $i => $row)
            @php
            $percentage = $totalIncome > 0 ? ($row->total / $totalIncome) * 100 : 0;
            $barColors = ['bg-emerald-500', 'bg-teal-500', 'bg-cyan-500', 'bg-sky-500', 'bg-blue-400'];
            $barColor = $barColors[$i % count($barColors)];
            $label = $row->payment_method ?? '—';
            @endphp
            <div class="mb-3">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-xs font-semibold text-gray-600 dark:text-slate-300">{{ $label }}</span>
                    <span class="text-xs text-gray-500 dark:text-slate-400">{{ 'Rp ' . number_format($row->total, 0, ',', '.') }}</span>
                </div>
                <div class="h-2 bg-gray-100 dark:bg-slate-700 rounded-full overflow-hidden">
                    <div class="{{ $barColor }} h-2 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                </div>
                <p class="text-right text-xs text-gray-400 dark:text-slate-500 mt-0.5">{{ $row->count }} transaksi</p>
            </div>
            @empty
            <p class="text-sm text-gray-400 dark:text-slate-500 text-center py-4">Tidak ada data</p>
            @endforelse
        </div>

        {{-- Breakdown Kategori --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-700 dark:text-slate-200 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                Per Kategori
            </h3>
            @forelse($categoryBreakdown as $i => $row)
            @php
            $percentage = $totalIncome > 0 ? ($row->total / $totalIncome) * 100 : 0;
            $barColors = ['bg-amber-500', 'bg-orange-500', 'bg-yellow-500', 'bg-lime-500', 'bg-green-500'];
            $barColor = $barColors[$i % count($barColors)];
            @endphp
            <div class="mb-3">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-xs font-semibold text-gray-600 dark:text-slate-300">{{ $row->category }}</span>
                    <span class="text-xs text-gray-500 dark:text-slate-400">{{ 'Rp ' . number_format($row->total, 0, ',', '.') }}</span>
                </div>
                <div class="h-2 bg-gray-100 dark:bg-slate-700 rounded-full overflow-hidden">
                    <div class="{{ $barColor }} h-2 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                </div>
                <p class="text-right text-xs text-gray-400 dark:text-slate-500 mt-0.5">{{ $row->count }} transaksi</p>
            </div>
            @empty
            <p class="text-sm text-gray-400 dark:text-slate-500 text-center py-4">Tidak ada data</p>
            @endforelse
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════
         TRANSACTION TABLE
    ════════════════════════════════════════════════════ --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 border-b border-gray-100 dark:border-slate-700">
            <h3 class="text-sm font-bold text-gray-700 dark:text-slate-200">Detail Transaksi Pemasukan</h3>
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 dark:text-slate-400">Tampilkan</label>
                <select wire:model.live="perPage" class="rounded-lg border border-gray-200 dark:border-slate-600 bg-gray-50 dark:bg-slate-700 text-gray-700 dark:text-white text-xs px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <label class="text-xs text-gray-500 dark:text-slate-400">baris</label>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-slate-700/50 text-left">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide">Tanggal</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide">Keterangan</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide hidden sm:table-cell">Kategori</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide hidden md:table-cell">Layanan</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide hidden md:table-cell">Metode Bayar</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-slate-700/50">
                    @forelse($transactions as $trx)
                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-4 py-3 text-gray-600 dark:text-slate-300 whitespace-nowrap text-xs">
                            {{ \Carbon\Carbon::parse($trx->transaction_date)->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-200 max-w-xs">
                            <span class="line-clamp-2 text-xs leading-relaxed">{{ $trx->description ?: '-' }}</span>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                                {{ $trx->category }}
                            </span>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell">
                            @if($trx->service_type)
                            @php
                            $svcColors = ['pppoe' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400', 'static' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400', 'hotspot' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-400'];
                            $svcColor = $svcColors[strtolower($trx->service_type)] ?? 'bg-gray-100 text-gray-600 dark:bg-slate-700 dark:text-slate-300';
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold {{ $svcColor }}">
                                {{ strtoupper($trx->service_type) }}
                            </span>
                            @else
                            <span class="text-gray-400 dark:text-slate-500 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell">
                            @if($trx->payment_method)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                                {{ $trx->payment_method }}
                            </span>
                            @else
                            <span class="text-gray-400 dark:text-slate-500 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap text-sm">
                            Rp {{ number_format($trx->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-12 h-12 text-gray-200 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-gray-400 dark:text-slate-500 text-sm">Tidak ada transaksi pada periode ini</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($transactions->count() > 0)
                <tfoot>
                    <tr class="bg-gray-50 dark:bg-slate-700/50 border-t border-gray-100 dark:border-slate-700">
                        <td colspan="5" class="px-4 py-3 text-xs font-bold text-gray-600 dark:text-slate-300">Total (halaman ini)</td>
                        <td class="px-4 py-3 text-right font-extrabold text-emerald-600 dark:text-emerald-400 text-sm">
                            Rp {{ number_format($transactions->sum('amount'), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        {{-- Pagination --}}
        @if($transactions->hasPages())
        <div class="px-5 py-4 border-t border-gray-100 dark:border-slate-700">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
</div>