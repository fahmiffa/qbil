<div class="space-y-6">
    <!-- Filters -->
    <div class="flex flex-wrap items-center justify-between gap-4 bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 transition-colors">
        <div class="flex items-center gap-2">
            <div class="p-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white">Laporan Keuangan</h2>
        </div>
        <div class="flex items-center gap-3">
            <select wire:model.live="month" class="bg-slate-50 dark:bg-slate-900 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors">
                @foreach(range(1, 12) as $m)
                    <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">{{ Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                @endforeach
            </select>
            <select wire:model.live="year" class="bg-slate-50 dark:bg-slate-900 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors">
                @foreach(range(now()->year - 2, now()->year + 1) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Pemasukan -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 relative overflow-hidden group transition-colors">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-16 h-16 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 14h-2v-2h2v2zm0-4h-2V7h2v5z"/></svg>
            </div>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Pemasukan (Lunas)</p>
            <h3 class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($stats->total_paid, 0, ',', '.') }}</h3>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-2"><span class="font-bold text-emerald-500">{{ $stats->count_paid }}</span> Tagihan Terbayar</p>
        </div>

        <!-- Piutang -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 relative overflow-hidden group transition-colors">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-16 h-16 text-amber-600" fill="currentColor" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/></svg>
            </div>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Piutang (Belum Bayar)</p>
            <h3 class="text-2xl font-bold text-amber-600 dark:text-amber-400">Rp {{ number_format($stats->total_unpaid, 0, ',', '.') }}</h3>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-2"><span class="font-bold text-amber-500">{{ $stats->count_unpaid }}</span> Pelanggan Menunggak</p>
        </div>

        @foreach($serviceBreakdown as $service)
        <!-- Service Type Card -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 transition-colors">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Layanan {{ $service->tipe }}</p>
            <h3 class="text-xl font-bold text-slate-800 dark:text-white">Rp {{ number_format($service->total, 0, ',', '.') }}</h3>
            <div class="flex items-center justify-between mt-1 mb-2">
                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">Potensi: Rp {{ number_format($service->potential, 0, ',', '.') }}</span>
                <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 text-nowrap">{{ $service->count }} User</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="flex-1 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-500" style="width: {{ $service->total > 0 ? ($service->paid / $service->total) * 100 : 0 }}%"></div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Table Details -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden transition-colors">
        <div class="p-5 border-b border-slate-50 dark:border-slate-700 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-800 dark:text-white">Rincian Status Pembayaran</h3>
            <div class="px-3 py-1 bg-slate-50 dark:bg-slate-700 rounded-full text-[10px] font-bold text-slate-500 dark:text-slate-300 uppercase tracking-widest border border-slate-100 dark:border-slate-600">
                Periode {{ Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50/50 dark:bg-slate-900/40 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Pelanggan</th>
                        <th class="px-6 py-4">Layanan</th>
                        <th class="px-6 py-4 text-right">Total Tagihan</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4">Tanggal Bayar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-700">
                    @forelse($invoices as $invoice)
                    <tr class="hover:bg-slate-50/30 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800 dark:text-white">{{ $invoice->customer->name }}</div>
                            <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">{{ $invoice->invoice_number }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $invoice->customer->package->tipe == 'PPPOE' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400' : 'bg-orange-50 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400' }}">
                                {{ $invoice->customer->package->tipe }} - {{ $invoice->customer->package->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-slate-700 dark:text-slate-300">
                            Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($invoice->status == 'paid')
                                <span class="bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 px-3 py-1 rounded-full text-[10px] font-bold uppercase">Lunas</span>
                            @elseif($invoice->status == 'unpaid')
                                <span class="bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 px-3 py-1 rounded-full text-[10px] font-bold uppercase">Belum Bayar</span>
                            @else
                                <span class="bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 px-3 py-1 rounded-full text-[10px] font-bold uppercase">Batal</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-500 dark:text-slate-400 text-xs">
                            {{ $invoice->paid_at ? $invoice->paid_at->format('d/m/Y H:i') : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500 italic font-medium">Tidak ada data invoice untuk periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
