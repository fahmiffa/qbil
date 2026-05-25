<div>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div>
                <h2 class="font-black text-xl text-slate-800 dark:text-white tracking-tight">Detail Pelanggan</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">

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
                        <div class="flex flex-col text-blue-200 text-sm mt-0.5">
                            @if($customer->phone)
                            <p>{{ $customer->phone }}</p>
                            @endif
                            @if($customer->phone2)
                            <p>{{ $customer->phone2 }}</p>
                            @endif
                            @if(!$customer->phone && !$customer->phone2)
                            <p>No. Telepon tidak tersedia</p>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $customer->status === 'active' ? 'bg-emerald-400/20 text-emerald-200' : 'bg-red-400/20 text-red-200' }}">
                                {{ $customer->status === 'active' ? '● Aktif' : '● Suspend' }}
                            </span>
                            <button wire:click="toggleNotify" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest transition-all {{ $customer->wa_notify ? 'bg-indigo-400/30 text-indigo-100 hover:bg-indigo-400/40' : 'bg-slate-400/20 text-slate-300 hover:bg-slate-400/30' }}">
                                {{ $customer->wa_notify ? 'Notif WA: ON' : 'Notif WA: OFF' }}
                            </button>
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
                        @if($customer->router)
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Router</p>
                            <p class="text-sm font-bold text-slate-800 dark:text-white">{{ $customer->router->name }}</p>
                            <p class="text-[10px] text-slate-500">{{ $customer->router->host }}</p>
                        </div>
                        @endif

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
            $paidInvoices = $customer->invoices()->where('status', 'paid')->count();
            $unpaidInvoices = $customer->invoices()->where('status', 'unpaid')->count();
            $totalPaid = $customer->invoices()->where('status', 'paid')->sum('amount');
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
                <div class="px-6 py-5 border-b border-slate-50 dark:border-slate-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-sm font-black text-slate-800 dark:text-white">Riwayat Invoice & Pembayaran</h3>
                        <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mt-0.5">{{ $totalInvoices }} total invoice tercatat</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(count($selected_invoices) > 0)
                        <a href="{{ route('print.invoices.bulk', ['ids' => implode(',', $selected_invoices)]) }}" target="_blank" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-black uppercase tracking-widest rounded-xl transition-all shadow-md shadow-emerald-500/20 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2v4h10z" />
                            </svg>
                            Print ({{ count($selected_invoices) }})
                        </a>
                        @endif
                        <button wire:click="openModal" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-black uppercase tracking-widest rounded-xl transition-all shadow-md shadow-blue-500/20">
                            + Bayar Manual
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-900/50 text-left">
                                <th class="px-4 py-4 w-10">
                                    <input type="checkbox" wire:model.live="select_all" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-blue-600 focus:ring-blue-500">
                                </th>
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
                                <td class="px-4 py-4">
                                    <input type="checkbox" wire:model.live="selected_invoices" value="{{ $invoice->id }}" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-blue-600 focus:ring-blue-500">
                                </td>
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
                                <td colspan="9" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500 italic text-sm">
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

    <!-- Modal Form Bayar Manual (Deposit Style) -->
    <div x-data="{ show: @entangle('isOpen') }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-[60] flex items-center justify-center p-4"
        style="display: none;">

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
            @click="show = false; $wire.closeModal()"></div>

        <!-- Modal Container -->
        <div wire:key="manual-payment-modal"
            class="relative bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-2xl transform transition-all w-full max-w-xl flex flex-col border border-slate-200 dark:border-slate-800 overflow-hidden">

            <form wire:submit.prevent="storePayment" class="flex flex-col max-h-[85vh]">
                <!-- Header -->
                <div class="px-8 py-6 border-b border-slate-50 dark:border-slate-800 flex-shrink-0">
                    <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">
                        Verifikasi Pembayaran Manual
                    </h3>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Pelanggan: {{ $customer->name }}</p>
                </div>

                <!-- Body (Scrollable) -->
                <div class="px-8 py-6 space-y-6 overflow-y-auto custom-scrollbar flex-1">

                    <!-- Pemilihan Bulan - Grid Style -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">Pilih Bulan Pembayaran</label>
                            <div class="flex items-center gap-2">
                                <select wire:model.live="selected_year" class="bg-slate-50 dark:bg-slate-950 border-none rounded-xl px-3 py-1 text-xs font-bold focus:ring-2 focus:ring-blue-500 outline-none transition-all dark:text-white shadow-sm ring-1 ring-slate-100 dark:ring-slate-800">
                                    @for($y = date('Y')-1; $y <= date('Y')+5; $y++)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                            @foreach($month_names as $num => $name)
                            @php
                            $monthKey = $selected_year . '-' . $num;
                            $isSelected = in_array($monthKey, $selected_months);
                            @endphp
                            <button type="button"
                                wire:click="toggleMonth('{{ $num }}')"
                                class="py-2 px-1 rounded-xl text-[9px] font-black uppercase tracking-wider transition-all border-2
                                    {{ $isSelected 
                                        ? 'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-600/20' 
                                        : 'bg-white dark:bg-slate-950 border-slate-100 dark:border-slate-800 text-slate-400 dark:text-slate-500 hover:border-blue-400' 
                                    }}">
                                {{ substr($name, 0, 3) }}
                            </button>
                            @endforeach
                        </div>
                        @error('months_count') <p class="text-red-500 text-[10px] font-bold">{{ $message }}</p> @enderror
                    </div>

                    <!-- Info & Tanggal Bayar -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-3 bg-blue-50/50 dark:bg-blue-900/10 rounded-2xl border border-blue-100/50 dark:border-blue-800/30">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-[9px] font-bold text-blue-600/70 uppercase tracking-wider">Durasi</span>
                                <span class="text-xs font-black text-blue-700 dark:text-blue-300">{{ $months_count }} Bulan</span>
                            </div>
                            <div class="text-[9px] text-slate-400 italic truncate">
                                @if(!empty($selected_months))
                                {{ \Carbon\Carbon::parse(min($selected_months))->format('M Y') }} - {{ \Carbon\Carbon::parse(max($selected_months))->format('M Y') }}
                                @endif
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-1.5">Tanggal Bayar</label>
                            <input type="datetime-local" wire:model="payment_date"
                                class="w-full bg-slate-50 dark:bg-slate-950 border-none rounded-2xl px-4 py-2.5 text-xs focus:ring-2 focus:ring-blue-500 outline-none transition-all dark:text-white font-bold h-[40px] shadow-inner ring-1 ring-slate-100 dark:ring-slate-800">
                        </div>
                    </div>

                    <!-- Input Harga Per Bulan (Optional Override) -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-1.5">Biaya Per Bulan (Manual)</label>
                        <input type="number" wire:model.live.debounce.500ms="amount_per_month" wire:change="calculateTotal"
                            class="w-full bg-slate-50 dark:bg-slate-950 border-none rounded-2xl px-4 py-3 text-xs focus:ring-2 focus:ring-blue-500 outline-none transition-all dark:text-white font-bold shadow-inner ring-1 ring-slate-100 dark:ring-slate-800">
                        @error('amount_per_month') <p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <!-- Total Calculation -->
                    <div class="bg-blue-50 dark:bg-slate-950 rounded-3xl p-6 overflow-hidden relative shadow-sm border border-blue-100 dark:border-slate-800">
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-bold text-blue-600/70 dark:text-slate-500 uppercase tracking-[0.2em]">Kalkulasi Total</span>
                                <span class="text-[10px] text-blue-600/70 dark:text-slate-500 font-bold uppercase tracking-widest">{{ $months_count }} Bln x Rp {{ number_format($amount_per_month, 0, ',', '.') }}</span>
                            </div>
                            <div class="text-3xl font-black text-blue-700 dark:text-white flex items-end gap-1">
                                <span class="text-sm text-blue-500 mb-1.5 font-black uppercase tracking-tighter">Rp</span>
                                {{ number_format($total_amount, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-blue-600/10 rounded-full blur-3xl"></div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-1.5">Catatan Internal</label>
                        <textarea wire:model="notes" rows="2" placeholder="Keterangan tambahan..."
                            class="w-full bg-slate-50 dark:bg-slate-950 border-none rounded-2xl px-4 py-3 text-xs focus:ring-2 focus:ring-blue-500 outline-none transition-all dark:text-white font-medium shadow-inner ring-1 ring-slate-100 dark:ring-slate-800"></textarea>
                    </div>
                </div>

                <!-- Footer (Stick to Modal) -->
                <div class="px-8 py-5 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-700/50 flex justify-end gap-3 flex-shrink-0">
                    <button type="button" @click="show = false; $wire.closeModal()" class="px-5 py-2.5 text-[10px] font-black text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 uppercase tracking-widest transition-all">Batal</button>
                    <button type="submit" wire:loading.attr="disabled"
                        class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-black rounded-2xl transition-all shadow-lg shadow-blue-600/20 uppercase tracking-widest flex items-center justify-center min-w-[150px]">
                        <span wire:loading.remove wire:target="storePayment">Simpan Pembayaran</span>
                        <div wire:loading.flex wire:target="storePayment" class="items-center justify-center gap-2 whitespace-nowrap">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Menyimpan...</span>
                        </div>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>