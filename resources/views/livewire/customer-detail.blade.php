<div>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('customers') }}" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="font-black text-xl text-slate-800 dark:text-white tracking-tight">Detail Pelanggan</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-widest font-semibold">{{ $customer->id_pelanggan }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Header Card --}}
            <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-3xl p-6 sm:p-8 shadow-xl shadow-blue-500/20 text-white overflow-hidden relative">
                <div class="absolute -top-10 -right-10 w-48 h-48 bg-white/5 rounded-full"></div>
                <div class="absolute -bottom-8 -left-8 w-32 h-32 bg-white/5 rounded-full"></div>
                <div class="relative flex flex-col sm:flex-row sm:items-center gap-5">
                    <div class="w-20 h-20 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-3xl font-black uppercase flex-shrink-0">
                        {{ substr($customer->name, 0, 1) }}
                    </div>
                    <div class="flex-1">
                        <h1 class="text-2xl font-black tracking-tight">{{ $customer->name }}</h1>
                        <p class="text-blue-200 text-sm mt-0.5">{{ $customer->phone ?? 'No. Telepon tidak tersedia' }}</p>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $customer->status === 'active' ? 'bg-emerald-400/20 text-emerald-200' : 'bg-red-400/20 text-red-200' }}">
                                {{ $customer->status === 'active' ? '● Aktif' : '● Suspend' }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-white/10 text-blue-100">
                                {{ strtoupper($customer->service_type) }}
                            </span>
                            @if($customer->package)
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-white/10 text-blue-100">
                                    {{ $customer->package->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right hidden sm:block">
                        <p class="text-xs text-blue-200 uppercase tracking-widest font-bold">ID Pelanggan</p>
                        <p class="text-2xl font-black font-mono mt-1">{{ $customer->id_pelanggan }}</p>
                        <p class="text-xs text-blue-200 mt-2">Terdaftar sejak {{ $customer->created_at->translatedFormat('d F Y') }}</p>
                    </div>
                </div>
            </div>

            {{-- Info Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                {{-- Info Umum --}}
                <div class="md:col-span-2 bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm p-6 space-y-4">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Informasi Pelanggan</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Alamat</p>
                            <p class="text-sm text-slate-700 dark:text-slate-200 font-medium">{{ $customer->address ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Keterangan</p>
                            <p class="text-sm text-slate-700 dark:text-slate-200 font-medium">{{ $customer->keterangan ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Jatuh Tempo</p>
                            <p class="text-sm font-bold text-slate-800 dark:text-white">{{ $customer->due_date ? $customer->due_date->translatedFormat('d F') : '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Tanggal Aktif</p>
                            <p class="text-sm font-bold text-slate-800 dark:text-white">{{ $customer->activated_at ? $customer->activated_at->translatedFormat('d F Y') : '-' }}</p>
                        </div>
                        @if($customer->latitude && $customer->longitude)
                        <div class="sm:col-span-2">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Koordinat</p>
                            <p class="text-xs font-mono text-slate-600 dark:text-slate-400">{{ $customer->latitude }}, {{ $customer->longitude }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Info Teknis --}}
                <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm p-6 space-y-4">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Info Teknis</h3>
                    <div class="space-y-3">
                        @if($customer->service_type === 'pppoe')
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Username PPPoE</p>
                                <p class="text-sm font-mono font-bold text-slate-800 dark:text-white">{{ $customer->username ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Profil PPPoE</p>
                                <p class="text-sm font-mono text-slate-700 dark:text-slate-300">{{ $customer->ppp_profile ?: '-' }}</p>
                            </div>
                        @else
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">IP Address</p>
                                <p class="text-sm font-mono font-bold text-slate-800 dark:text-white">{{ $customer->ip_address ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">MAC Address</p>
                                <p class="text-xs font-mono text-slate-700 dark:text-slate-300">{{ $customer->mac_address ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">DHCP Server</p>
                                <p class="text-sm text-slate-700 dark:text-slate-300">{{ $customer->dhcp_server ?: '-' }}</p>
                            </div>
                        @endif

                        @if($customer->asset)
                            <div class="pt-2 border-t border-slate-50 dark:border-slate-700">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Titik Jaringan</p>
                                <p class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ $customer->asset->category }} — {{ $customer->asset->name }}</p>
                                @if($customer->asset->address)
                                    <p class="text-[10px] text-slate-500 mt-0.5">{{ $customer->asset->address }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Statistik Invoice --}}
            @php
                $totalInvoices = $customer->invoices()->count();
                $paidInvoices  = $customer->invoices()->where('status', 'paid')->count();
                $unpaidInvoices = $customer->invoices()->where('status', 'unpaid')->count();
                $totalPaid     = $customer->invoices()->where('status', 'paid')->sum('amount');
            @endphp
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-5 text-center">
                    <p class="text-2xl font-black text-slate-800 dark:text-white">{{ $totalInvoices }}</p>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">Total Invoice</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-5 text-center">
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $paidInvoices }}</p>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">Lunas</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-5 text-center">
                    <p class="text-2xl font-black text-amber-500 dark:text-amber-400">{{ $unpaidInvoices }}</p>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">Belum Lunas</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-5 text-center">
                    <p class="text-lg font-black text-blue-600 dark:text-blue-400">Rp {{ number_format($totalPaid, 0, ',', '.') }}</p>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">Total Terbayar</p>
                </div>
            </div>

            {{-- Riwayat Invoice --}}
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-50 dark:border-slate-700">
                    <h3 class="text-sm font-black text-slate-800 dark:text-white">Riwayat Invoice & Pembayaran</h3>
                    <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mt-0.5">{{ $totalInvoices }} total invoice tercatat</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-900/50 text-left">
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">No. Invoice</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Periode</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tagihan</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Total + Unik</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Jatuh Tempo</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tgl Bayar</th>
                                <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-700/50">
                            @forelse($invoices as $invoice)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="text-xs font-mono font-bold text-slate-800 dark:text-white">{{ $invoice->invoice_number }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                            {{ \Carbon\Carbon::parse($invoice->billing_period)->translatedFormat('F Y') }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-xs font-black text-slate-800 dark:text-white">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</p>
                                        <p class="text-[9px] text-emerald-600 dark:text-emerald-400">+{{ $invoice->unique_code }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-xs text-slate-600 dark:text-slate-400">{{ $invoice->due_date->format('d/m/Y') }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($invoice->status === 'paid')
                                            <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">Lunas</span>
                                        @elseif($invoice->status === 'unpaid')
                                            <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">Belum Lunas</span>
                                        @elseif($invoice->status === 'unpaid_piutang')
                                            <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-orange-50 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400">Piutang</span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400">{{ ucfirst($invoice->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-xs text-slate-600 dark:text-slate-400">{{ $invoice->paid_at ? $invoice->paid_at->translatedFormat('d F Y') : '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('public.invoice', $invoice->id) }}" target="_blank"
                                            class="text-[10px] font-black text-blue-600 dark:text-blue-400 hover:underline uppercase tracking-widest">
                                            Lihat
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500 italic text-sm">
                                        Belum ada riwayat invoice untuk pelanggan ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($invoices->hasPages())
                    <div class="px-6 py-4 border-t border-slate-50 dark:border-slate-700">
                        {{ $invoices->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
