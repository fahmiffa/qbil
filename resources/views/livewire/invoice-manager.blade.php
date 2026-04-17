<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Manajemen Invoice</h2>
    </x-slot>

    <div class="w-full">
        <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-2xl border border-gray-100 dark:border-slate-700 transition-colors">
            <div class="p-4 sm:p-6">
                


                <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                    <div class="flex items-center gap-4">
                        <div>
                            <input type="month" wire:model.live="billing_period" class="bg-slate-50 dark:bg-slate-900 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors">
                        </div>
                        <button wire:click="generateInvoices" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold py-2.5 px-5 rounded-xl transition-all shadow-lg shadow-blue-600/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            Generate Tagihan Bulan Ini
                        </button>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                        <div class="flex items-center gap-2">
                             <select wire:model.live="perPage" class="bg-slate-50 dark:bg-slate-900 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors">
                                <option value="10">10 Baris</option>
                                <option value="50">50 Baris</option>
                                <option value="100">100 Baris</option>
                                <option value="1000">1000 Baris</option>
                                <option value="all">Semua</option>
                            </select>
                        </div>

                        <div class="relative flex-1 md:w-64">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari invoice/pelanggan/kode unik..." class="w-full pl-10 bg-slate-50 dark:bg-slate-900 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors">
                        </div>
                        
                        <select wire:model.live="filter_status" class="bg-slate-50 dark:bg-slate-900 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors">
                            <option value="">Semua Status</option>
                            <option value="unpaid">Belum Lunas</option>
                            <option value="paid">Lunas</option>
                            <option value="canceled">Dibatalkan</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700 transition-colors">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50/50 dark:bg-slate-900/40">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">No. Invoice</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Pelanggan</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Periode</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Total Tagihan</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Batas Waktu</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700 text-sm transition-colors">
                            @forelse($invoices as $invoice)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-all">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-mono font-bold text-slate-700 dark:text-slate-300">{{ $invoice->invoice_number }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-900 dark:text-white">{{ $invoice->customer->name }}</span>
                                            <span class="text-xs text-slate-400 dark:text-slate-500">{{ $invoice->customer->username }} ({{ $invoice->customer->package->name ?? '-' }})</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-400">
                                        {{ $invoice->billing_period }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-900 dark:text-white">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                                            <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium">Uniq: +{{ $invoice->unique_code }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-400">
                                        {{ $invoice->due_date->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($invoice->status == 'unpaid')
                                            <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 uppercase tracking-wider">Unpaid</span>
                                        @elseif($invoice->status == 'paid')
                                            <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Paid</span>
                                        @else
                                            <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 uppercase tracking-wider">Canceled</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                        @if($invoice->status == 'unpaid')
                                            <button wire:click="markAsPaid('{{ $invoice->id }}')" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 font-bold text-xs" wire:confirm="Yakin tandai LUNAS?">Lunas</button>
                                            <button wire:click="cancelInvoice('{{ $invoice->id }}')" class="text-slate-400 dark:text-slate-500 hover:text-red-600 dark:hover:text-red-400 font-bold text-xs" wire:confirm="Yakin batalkan invoice?">Batal</button>
                                        @elseif($invoice->status == 'canceled')
                                            <button wire:click="regenerateInvoice('{{ $invoice->id }}')" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-bold text-xs" wire:confirm="Generate ulang invoice ini dengan kode unik baru?">Generate Lagi</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500 italic">Belum ada invoice untuk periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $invoices->links() }}
                </div>

            </div>
        </div>
    </div>
</div>
