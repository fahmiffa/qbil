<div>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-slate-800 dark:text-white tracking-tight">Piutang</h2>
    </x-slot>

    <div class="w-full">
        <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-lg border border-gray-100 dark:border-slate-700 transition-colors">
            <div class="p-4 sm:p-6 text-gray-900 dark:text-gray-100">
                <div class="pb-4 border-b border-slate-50 dark:border-slate-700 flex flex-wrap items-center justify-between gap-4 mb-4">
                    <div class="flex items-center gap-4 flex-1 min-w-0">
                        <div class="relative w-full sm:w-80">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari Nama Pelanggan..." 
                                class="w-full pl-11 bg-slate-50 dark:bg-slate-900/50 border-none rounded-2xl py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all dark:text-white">
                        </div>
                        <select wire:model.live="filterStatus" class="bg-slate-50 dark:bg-slate-900 dark:text-slate-300 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                            <option value="">Semua Status</option>
                            <option value="unpaid">Belum Bayar</option>
                            <option value="paid">Sudah Lunas</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-900/50 text-left">
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Pelanggan</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Daftar Periode</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Total Piutang</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-widest">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-700">
                            @forelse($customers as $customer)
                                @php 
                                    $totalAmount = $customer->piutangs->sum('amount');
                                    $hasUnpaid = $customer->piutangs->where('status', 'unpaid')->count() > 0;
                                @endphp
                                <tr wire:key="cust-{{ $customer->id }}" class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center font-black text-sm uppercase">
                                                {{ substr($customer->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-800 dark:text-white">{{ $customer->name }}</p>
                                                <p class="text-[10px] text-slate-400 font-mono tracking-tighter">{{ $customer->id_pelanggan }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex flex-col gap-1.5 max-w-xs items-start">
                                            @foreach($customer->piutangs as $item)
                                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold border flex items-center justify-between min-w-[140px] {{ $item->status == 'paid' ? 'bg-emerald-50 border-emerald-100 text-emerald-600 dark:bg-emerald-900/20 dark:border-emerald-800/50 dark:text-emerald-400' : 'bg-slate-50 border-slate-100 text-slate-600 dark:bg-slate-900 dark:border-slate-800 dark:text-slate-400' }}">
                                                    <span>{{ \Carbon\Carbon::parse($item->billing_period)->translatedFormat('F Y') }}</span>
                                                    <span class="ml-3 opacity-80 font-black">Rp {{ number_format($item->amount, 0, ',', '.') }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <p class="font-black text-slate-800 dark:text-white text-sm">Rp {{ number_format($totalAmount, 0, ',', '.') }}</p>
                                        <p class="text-[9px] text-slate-400 uppercase tracking-widest font-bold">{{ $customer->piutangs->count() }} Invoice</p>
                                    </td>
                                    <td class="px-6 py-5 uppercase">
                                        @if($hasUnpaid)
                                            <div class="flex items-center gap-1.5">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                <span class="text-[10px] font-black tracking-widest text-amber-600 dark:text-amber-400">Terutang</span>
                                            </div>
                                        @else
                                            <div class="flex flex-col gap-0.5">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    <span class="text-[10px] font-black tracking-widest text-emerald-600 dark:text-emerald-400">Lunas</span>
                                                </div>
                                                @php $latestPaid = $customer->piutangs->whereNotNull('paid_at')->max('paid_at'); @endphp
                                                @if($latestPaid)
                                                    <p class="text-[8px] text-slate-400 normal-case font-medium">Brt: {{ $latestPaid instanceof \Carbon\Carbon ? $latestPaid->format('d/m/Y') : \Carbon\Carbon::parse($latestPaid)->format('d/m/Y') }}</p>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5 text-right flex items-center justify-end gap-2">
                                        <a href="{{ route('print.piutang', $customer->piutangs->first()->id) }}" target="_blank" 
                                            class="p-2 text-slate-400 hover:text-blue-600 transition-colors" title="Cetak Riwayat">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        </a>

                                        @if($hasUnpaid)
                                            <button wire:click="markAsPaid('{{ $customer->id }}')" 
                                                class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-[10px] font-black uppercase tracking-widest transition-all shadow-lg shadow-blue-600/20">
                                                Verifikasi Lunas
                                            </button>
                                        @endif
                                        
                                        <button @click="Swal.fire({
                                            title: 'Hapus Riwayat?',
                                            text: 'Menghapus data piutang tidak akan menghapus invoice terkait.',
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonColor: '#ef4444',
                                            cancelButtonColor: '#64748b',
                                            confirmButtonText: 'Ya, Hapus',
                                            background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff',
                                            color: document.documentElement.classList.contains('dark') ? '#f1f5f9' : '#0f172a'
                                        }).then((result) => { if (result.isConfirmed) { $wire.delete('{{ $customer->piutangs->first()->id }}') } })" 
                                            class="p-2 text-slate-300 hover:text-red-500 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">Belum ada data piutang ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-6">
                    {{ $customers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
