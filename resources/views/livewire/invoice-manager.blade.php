<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Invoice</h2>
    </x-slot>

    <div class="w-full">
        @if($isBeforeIsolation && $customersToIsolate->isNotEmpty())
            <div class="mb-6 group">
                <div @click="$wire.set('showIsolationModal', true)" class="cursor-pointer bg-gradient-to-r from-rose-500/10 via-rose-500/5 to-transparent border border-rose-200 dark:border-rose-900/50 rounded-2xl p-4 flex items-center justify-between transition-all hover:shadow-lg hover:shadow-rose-500/10">
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <div class="absolute inset-0 bg-rose-500 rounded-full animate-ping opacity-25"></div>
                            <div class="relative w-12 h-12 bg-rose-500 text-white rounded-full flex items-center justify-center shadow-lg shadow-rose-500/20">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-rose-700 dark:text-rose-400 font-black tracking-tight">PERINGATAN ISOLIR OTOMATIS</h3>
                            <p class="text-sm text-rose-600/80 dark:text-rose-500/80 font-medium">
                                Ada <span class="font-bold underline">{{ $customersToIsolate->count() }} pelanggan</span> yang akan diisolir otomatis dalam <span class="font-bold text-rose-700 dark:text-rose-300">{{ $isolationTimeRemaining }}</span>.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 bg-rose-500 hover:bg-rose-600 text-white px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-md">
                        <span>Lihat Daftar</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-2xl border border-gray-100 dark:border-slate-700 transition-colors">
            <div class="p-4 sm:p-6">
                


                <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                    <div class="flex items-center gap-4">
                        <div>
                            <select wire:model.live="billing_period" class="bg-slate-50 dark:bg-slate-900 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors min-w-[150px]">
                                @php $currentYear = date('Y'); @endphp
                                @for($i = 1; $i <= 12; $i++)
                                    @php
                                        $monthValue = $currentYear . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
                                        $monthLabel = \Carbon\Carbon::create()->month($i)->translatedFormat('F');
                                    @endphp
                                    <option value="{{ $monthValue }}">{{ $monthLabel }}</option>
                                @endfor
                            </select>
                        </div>
                        <button wire:click="openGenerateModal" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold py-2.5 px-5 rounded-xl transition-all shadow-lg shadow-blue-600/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            Generate Tagihan ({{ \Carbon\Carbon::parse($billing_period)->translatedFormat('F Y') }})
                        </button>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                        <div class="flex items-center gap-2">
                             <select wire:model.live="perPage" class="bg-slate-50 dark:bg-slate-900 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors">
                                <option value="10">Tampil 10</option>
                                <option value="50">Tampil 50</option>
                                <option value="100">Tampil 100</option>
                                <option value="1000">Tampil 1000</option>
                                <option value="all">Tampil Semua</option>
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

                <div wire:poll.keep-alive.10s class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700 transition-colors">
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
                                <tr wire:key="invoice-{{ $invoice->id }}" class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-all">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-mono font-bold text-slate-700 dark:text-slate-300">{{ $invoice->invoice_number }}</span>
                                    </td>
                                    <td class="whitespace-nowrap p-0">
                                        <a href="{{ route('customers.detail', $invoice->customer_id) }}" class="block px-6 py-4 transition-colors group" wire:navigate>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $invoice->customer->name }}</span>
                                                <span class="text-xs text-slate-400 dark:text-slate-500">{{ $invoice->customer->username }} ({{ $invoice->customer->package->name ?? '-' }})</span>
                                            </div>
                                        </a>
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
                                        {{ $invoice->due_date->format('d') }}
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
                                    <td class="px-6 py-4 whitespace-nowrap text-right space-x-3">
                                        <div class="inline-flex gap-2">
                                            <button @click="
                                                navigator.clipboard.writeText('{{ route('public.invoice', $invoice->id) }}');
                                                $dispatch('toast', { type: 'success', message: 'Link invoice berhasil disalin!' });
                                            " class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-bold text-xs flex-shrink-0">
                                                Copy
                                            </button>
                                            <a href="{{ route('public.invoice', $invoice->id) }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-bold text-xs flex-shrink-0">
                                                Open
                                            </a>
                                            <button wire:click="sendWhatsappNotification('{{ $invoice->id }}')" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 font-bold text-xs flex-shrink-0">
                                                Kirim WA
                                            </button>
                                            <a href="{{ route('public.print-thermal', $invoice->id) }}" target="_blank" class="text-slate-400 dark:text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 font-bold text-xs flex-shrink-0">
                                                Thermal
                                            </a>
                                        </div>


                                        @if($invoice->status == 'unpaid' && !$invoice->paid_at)
                                            <button wire:click="openVerifyModal('{{ $invoice->id }}')" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 font-bold text-xs">Verifikasi</button>
                                            
                                            <button @click="
                                                Swal.fire({
                                                    title: 'Batalkan Invoice?',
                                                    text: 'Invoice {{ $invoice->invoice_number }} akan ditandai sebagai dibatalkan.',
                                                    icon: 'warning',
                                                    showCancelButton: true,
                                                    confirmButtonColor: '#f59e0b',
                                                    cancelButtonColor: '#64748b',
                                                    confirmButtonText: 'Ya, Batalkan',
                                                    cancelButtonText: 'Kembali',
                                                    background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff',
                                                    color: document.documentElement.classList.contains('dark') ? '#f1f5f9' : '#0f172a'
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        $wire.cancelInvoice('{{ $invoice->id }}')
                                                    }
                                                })
                                            " class="text-amber-600 dark:text-amber-500 hover:text-amber-800 dark:hover:text-amber-400 font-bold text-xs">Batal</button>
                                        @elseif($invoice->status == 'canceled')
                                            <button @click="
                                                Swal.fire({
                                                    title: 'Re-generate Invoice?',
                                                    text: 'Buat ulang invoice ini dengan kode unik yang baru?',
                                                    icon: 'info',
                                                    showCancelButton: true,
                                                    confirmButtonColor: '#3b82f6',
                                                    cancelButtonColor: '#64748b',
                                                    confirmButtonText: 'Ya, Generate Ulang',
                                                    cancelButtonText: 'Kembali',
                                                    background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff',
                                                    color: document.documentElement.classList.contains('dark') ? '#f1f5f9' : '#0f172a'
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        $wire.regenerateInvoice('{{ $invoice->id }}')
                                                    }
                                                })
                                            " class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-bold text-xs">Generate Lagi</button>
                                        @endif

                                        <button @click="
                                            Swal.fire({
                                                title: 'Hapus Permanen?',
                                                text: 'Data invoice {{ $invoice->invoice_number }} akan dihapus selamanya dari database.',
                                                icon: 'error',
                                                showCancelButton: true,
                                                confirmButtonColor: '#ef4444',
                                                cancelButtonColor: '#64748b',
                                                confirmButtonText: 'Ya, Hapus Permanen',
                                                cancelButtonText: 'Batal',
                                                background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff',
                                                color: document.documentElement.classList.contains('dark') ? '#f1f5f9' : '#0f172a'
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    $wire.deleteInvoice('{{ $invoice->id }}')
                                                }
                                            })
                                        " class="text-slate-400 dark:text-slate-500 hover:text-red-600 dark:hover:text-red-400 font-bold text-xs">Hapus</button>
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

    <!-- Verification Modal (Lunas / Piutang) -->
    <div x-data="{ show: @entangle('showVerifyModal') }" 
         x-show="show" 
         x-cloak
         class="fixed inset-0 z-[60] overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="show = false; $wire.closeVerifyModal()">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            @if($selectedInvoice)
                <div wire:key="verify-modal-content" class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 dark:border-slate-700">
                    <div class="px-8 py-6 border-b border-slate-50 dark:border-slate-700 text-center">
                        <div class="w-16 h-16 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Verifikasi Pembayaran</h3>
                        <p class="text-sm text-slate-500 mt-1">{{ $selectedInvoice->invoice_number }} - {{ $selectedInvoice->customer->name }}</p>
                    </div>

                    <div class="px-8 py-8 space-y-4">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Tanggal Bayar</label>
                                <input type="datetime-local" wire:model="paid_at" class="w-full bg-slate-50 dark:bg-slate-900 dark:text-slate-200 border-none rounded-2xl px-5 py-4 focus:ring-2 focus:ring-blue-500 transition-all font-bold">
                            </div>

                            <p class="text-sm text-slate-600 dark:text-slate-400 text-center mb-6">Pilih status penyelesaian untuk tagihan ini. Keduanya akan membuka akses internet pelanggan jika terisolir.</p>
                        </div>
                        
                        <button wire:click="markAsPaid" class="w-full flex items-center justify-between p-4 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:hover:bg-emerald-900/30 border border-emerald-100 dark:border-emerald-800/30 rounded-2xl transition-all group">
                            <div class="text-left">
                                <p class="font-black text-emerald-700 dark:text-emerald-400">TANDAI LUNAS</p>
                                <p class="text-xs text-emerald-600/70">Pembayaran telah diterima tunai/transfer</p>
                            </div>
                            <svg class="w-6 h-6 text-emerald-500 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>

                        <button wire:click="markAsPiutang" class="w-full flex items-center justify-between p-4 bg-amber-50 hover:bg-amber-100 dark:bg-amber-900/20 dark:hover:bg-amber-900/30 border border-amber-100 dark:border-amber-800/30 rounded-2xl transition-all group">
                            <div class="text-left">
                                <p class="font-black text-amber-700 dark:text-amber-400">JADIKAN PIUTANG</p>
                                <p class="text-xs text-amber-600/70">Bayar nanti, internet tetap dibuka</p>
                            </div>
                            <svg class="w-6 h-6 text-amber-500 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </div>

                    <div class="px-8 py-4 bg-slate-50 dark:bg-slate-900/30 text-center border-t border-slate-50 dark:border-slate-700">
                        <button @click="show = false; $wire.closeVerifyModal()" class="text-xs font-black text-slate-400 hover:text-slate-600 uppercase tracking-widest transition-all">Batal</button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Generate Invoice Modal (Bulk/Selection) -->
    <div x-data="{ show: @entangle('showGenerateModal') }" 
         x-show="show" 
         x-cloak
         class="fixed inset-0 z-[60] overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="show = false">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-8 py-6 border-b border-slate-50 dark:border-slate-700">
                    <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Generate Tagihan</h3>
                    <p class="text-sm text-slate-500 mt-1">Periode: {{ \Carbon\Carbon::parse($billing_period)->translatedFormat('F Y') }}</p>
                </div>

                <div class="px-8 py-6 space-y-6">
                    <div x-data="{ openSuggest: false }" @click.away="openSuggest = false">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Pilih Pelanggan (Kosongkan untuk SEMUA)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                            <input 
                                x-ref="searchInput"
                                wire:model.live.debounce.300ms="customerSearch" 
                                @input="openSuggest = true"
                                type="text" 
                                placeholder="Cari nama/ID/Username..." 
                                class="w-full pl-10 bg-slate-50 dark:bg-slate-900 dark:text-slate-200 border-none rounded-2xl px-5 py-3.5 focus:ring-2 focus:ring-blue-500 transition-all font-bold text-sm shadow-inner"
                                autocomplete="off"
                                x-init="$watch('show', value => { if(value) setTimeout(() => $refs.searchInput.focus(), 100) })"
                            >
                            
                            {{-- Loading Indicator --}}
                            <div wire:loading wire:target="customerSearch" class="absolute right-4 top-1/2 -translate-y-1/2">
                                <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>

                        {{-- Search Results / Suggestions --}}
                        <div x-show="openSuggest && $wire.customerSearch.length >= 2" class="relative">
                            <div class="absolute z-[70] mt-2 w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-2xl max-h-64 overflow-y-auto ring-1 ring-black ring-opacity-5">
                                @if(!empty($modalCustomers))
                                    @foreach($modalCustomers as $c)
                                        <button 
                                            wire:click="toggleCustomer('{{ $c->id }}')" 
                                            @click="openSuggest = false; $wire.customerSearch = ''"
                                            class="w-full px-4 py-3 text-left hover:bg-blue-50 dark:hover:bg-blue-900/20 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between group transition-colors">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-xs font-bold text-slate-500">
                                                    {{ substr($c->name, 0, 1) }}
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-bold text-slate-700 dark:text-slate-300 group-hover:text-blue-600 transition-colors">{{ $c->name }}</span>
                                                    <span class="text-[10px] text-slate-400 font-medium">{{ $c->id_pelanggan }} | {{ $c->username }}</span>
                                                </div>
                                            </div>
                                            @if(in_array($c->id, $selectedCustomers))
                                                <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            @else
                                                <svg class="w-5 h-5 text-slate-200 dark:text-slate-700 group-hover:text-blue-200" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/></svg>
                                            @endif
                                        </button>
                                    @endforeach
                                @else
                                    <div class="px-4 py-8 text-center" wire:loading.remove wire:target="customerSearch">
                                        <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <p class="text-xs text-slate-400 font-bold">Pelanggan tidak ditemukan</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Selected Customers Chips --}}
                    @if(!empty($selectedCustomerObjects))
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Pelanggan Terpilih ({{ count($selectedCustomers) }})</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($selectedCustomerObjects as $sc)
                                    <span class="inline-flex items-center gap-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 px-3 py-1.5 rounded-xl text-xs font-bold border border-blue-100 dark:border-blue-800/50">
                                        {{ $sc->name }}
                                        <button wire:click="toggleCustomer('{{ $sc->id }}')" class="hover:text-red-500 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="bg-amber-50 dark:bg-amber-900/20 p-4 rounded-2xl border border-amber-100 dark:border-amber-800/30">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-xs text-amber-700 dark:text-amber-400 leading-relaxed font-medium">
                                @if(empty($selectedCustomers))
                                    Sistem akan membuat tagihan untuk <strong>SEMUA</strong> pelanggan aktif yang belum memiliki tagihan di periode ini.
                                @else
                                    Sistem akan membuat tagihan hanya untuk <strong>{{ count($selectedCustomers) }}</strong> pelanggan terpilih di atas.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="px-8 py-6 bg-slate-50 dark:bg-slate-900/30 flex items-center justify-between border-t border-slate-50 dark:border-slate-700">
                    <button @click="show = false" class="text-xs font-black text-slate-400 hover:text-slate-600 uppercase tracking-widest transition-all">Batal</button>
                    
                    <button wire:click="generateInvoices" 
                            class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-black py-3 px-8 rounded-2xl transition-all shadow-lg shadow-blue-600/20 uppercase tracking-widest">
                        Konfirmasi Generate
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Isolation List Modal -->
    <div x-data="{ show: @entangle('showIsolationModal') }" 
         x-show="show" 
         x-cloak
         class="fixed inset-0 z-[60] overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="show = false">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-8 py-6 border-b border-slate-50 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Daftar Antrean Isolir</h3>
                        <p class="text-sm text-slate-500 mt-1">Pelanggan di bawah ini akan diisolir otomatis hari ini</p>
                    </div>
                    <button @click="show = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-0 max-h-[60vh] overflow-y-auto">
                    <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-700">
                        <thead class="bg-slate-50/50 dark:bg-slate-900/40 sticky top-0 z-10 backdrop-blur-md">
                            <tr>
                                <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Pelanggan</th>
                                <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Username</th>
                                <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Tagihan</th>
                                <th class="px-8 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @foreach($customersToIsolate as $c)
                                @php
                                    $unpaidInvoice = $c->invoices()->where('status', 'unpaid')->where('billing_period', now()->format('Y-m'))->first();
                                @endphp
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-8 py-4">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-900 dark:text-white">{{ $c->name }}</span>
                                            <span class="text-[10px] text-slate-400 font-medium">{{ $c->id_pelanggan }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-4 font-mono text-xs text-slate-600 dark:text-slate-400">
                                        {{ $c->username }}
                                    </td>
                                    <td class="px-8 py-4">
                                        @if($unpaidInvoice)
                                            <span class="font-bold text-rose-600 dark:text-rose-400">Rp {{ number_format($unpaidInvoice->total_amount, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-slate-400 italic text-xs">Data tidak sinkron</span>
                                        @endif
                                    </td>
                                    <td class="px-8 py-4 text-right">
                                        @if($unpaidInvoice)
                                            <button wire:click="openVerifyModal('{{ $unpaidInvoice->id }}')" @click="show = false" class="bg-emerald-500 hover:bg-emerald-600 text-white text-[10px] font-black px-3 py-1.5 rounded-lg shadow-sm transition-all uppercase">Verifikasi</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-8 py-6 bg-slate-50 dark:bg-slate-900/30 text-center border-t border-slate-50 dark:border-slate-700">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Lakukan verifikasi pembayaran untuk membatalkan isolir otomatis.</p>
                </div>
            </div>
        </div>
    </div>
</div>

