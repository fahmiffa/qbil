<div>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-slate-800 dark:text-white tracking-tight">Tagihan</h2>
            </div>
        </div>
    </x-slot>

    <div class="w-full">
        @if(auth()->user()->features()->whereIn('parameter', ['pppoe', 'static'])->exists())
        @if($isBeforeIsolation && $customersToIsolate->isNotEmpty())
        <div x-show="!$wire.isAlertDismissed" class="mb-6 group relative">
            <div @click="$wire.set('showIsolationModal', true)" class="cursor-pointer bg-gradient-to-r from-rose-500/10 via-rose-500/5 to-transparent border border-rose-200 dark:border-rose-500/20 rounded-2xl p-4 flex items-center justify-between transition-all hover:shadow-lg hover:shadow-rose-500/10">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div class="absolute inset-0 bg-rose-500 rounded-full animate-ping opacity-25"></div>
                        <div class="relative w-12 h-12 bg-rose-500 text-white rounded-full flex items-center justify-center shadow-lg shadow-rose-500/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-rose-700 dark:text-rose-400 font-black tracking-tight">PERINGATAN ISOLIR OTOMATIS</h3>
                        <p class="text-sm text-rose-600/90 dark:text-rose-200/90 font-medium">
                            Ada <span class="font-bold underline text-rose-800 dark:text-rose-100">{{ $customersToIsolate->count() }} pelanggan</span> yang akan diisolir otomatis dalam <span class="font-bold text-rose-700 dark:text-rose-300">{{ $isolationTimeRemaining }}</span>.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 bg-rose-500 hover:bg-rose-600 text-white px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-md">
                        <span>Lihat Daftar</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                    <button @click.stop="$wire.set('isAlertDismissed', true)" class="p-2 text-rose-400 hover:text-rose-600 dark:hover:text-rose-200 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        @endif
        @endif

        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-2xl border border-gray-100 dark:border-slate-700 transition-colors">
            <div class="p-4 sm:p-6">



                <div class="flex flex-col gap-6 mb-8">
                    {{-- Row 1: Period and Generate --}}
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <div class="relative min-w-[180px]">
                                <select wire:model.live="billing_period" class="w-full bg-slate-50 dark:bg-slate-900 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm font-bold focus:ring-2 focus:ring-blue-500 outline-none appearance-none transition-all">
                                    @php $currentYear = date('Y'); @endphp
                                    @for($i = 1; $i <= 12; $i++)
                                        @php
                                        $monthValue=$currentYear . '-' . str_pad($i, 2, '0' , STR_PAD_LEFT);
                                        $monthLabel=\Carbon\Carbon::create()->month($i)->translatedFormat('F');
                                        @endphp
                                        <option value="{{ $monthValue }}">{{ $monthLabel }} {{ $currentYear }}</option>
                                        @endfor
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                            @if(auth()->user()->features()->whereIn('parameter', ['pppoe', 'static'])->exists())
                            <button wire:click="openGenerateModal" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-black py-2.5 px-6 rounded-xl transition-all shadow-lg shadow-blue-600/20 active:scale-95">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Generate Tagihan
                            </button>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 sm:justify-end">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest hidden sm:block">Tampil:</span>
                            <select wire:model.live="perPage" class="bg-slate-50 dark:bg-slate-900 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-sm font-bold focus:ring-2 focus:ring-blue-500 outline-none transition-colors">
                                <option value="10">10</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="1000">1000</option>
                                <option value="all">Semua</option>
                            </select>
                        </div>
                    </div>

                    {{-- Row 2: Search and Filters --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div class="relative sm:col-span-2 lg:col-span-1">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari invoice/pelanggan/kode unik..." class="w-full pl-11 bg-slate-50 dark:bg-slate-900 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all shadow-sm">
                        </div>

                        <div class="relative">
                            <select wire:model.live="filter_status" class="w-full bg-slate-50 dark:bg-slate-900 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500 outline-none appearance-none transition-all">
                                <option value="">Semua Status</option>
                                <option value="unpaid">Belum Lunas</option>
                                <option value="paid">Lunas</option>
                                <option value="canceled">Dibatalkan</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        <div class="relative">
                            <select wire:model.live="filter_due_date" class="w-full bg-slate-50 dark:bg-slate-900 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500 outline-none appearance-none transition-all">
                                <option value="">Semua Tgl. Jatuh Tempo</option>
                                @for($i = 1; $i <= 31; $i++)
                                    <option value="{{ $i }}">Tanggal {{ $i }}</option>
                                    @endfor
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        @if(auth()->user()->hasFeature('mikrotik'))
                        <div class="relative">
                            <select wire:model.live="filter_router" class="w-full bg-slate-50 dark:bg-slate-900 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500 outline-none appearance-none transition-all">
                                <option value="">Semua Router</option>
                                @foreach($routers as $router)
                                <option value="{{ $router->id }}">{{ $router->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        @endif

                        <div class="relative">
                            <select wire:model.live="filter_service_type" class="w-full bg-slate-50 dark:bg-slate-900 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500 outline-none appearance-none transition-all">
                                <option value="">Semua Tipe Layanan</option>
                                @if(in_array('pppoe', $availableServiceTypes))
                                <option value="pppoe">PPPoE</option>
                                @endif
                                @if(in_array('static', $availableServiceTypes))
                                <option value="static">Static</option>
                                @endif
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        @if(count($user_payment_methods) > 0)
                        <div class="relative">
                            <select wire:model.live="filter_payment_method" class="w-full bg-slate-50 dark:bg-slate-900 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500 outline-none appearance-none transition-all">
                                <option value="">Semua Metode Bayar</option>
                                @foreach($user_payment_methods as $method)
                                <option value="{{ $method->nama }}">{{ $method->nama }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Progress Bar Generate Invoice --}}
                @if($invoiceProgress)
                <div wire:poll.1500ms="checkInvoiceProgress" class="mb-4 p-4 rounded-2xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/50">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm font-bold text-blue-700 dark:text-blue-300">Men-generate Tagihan...</span>
                        </div>
                        <span class="text-sm font-mono font-bold text-blue-600 dark:text-blue-400">
                            {{ $invoiceProgress['current'] ?? 0 }} / {{ $invoiceProgress['total'] ?? 0 }}
                        </span>
                    </div>
                    <div class="w-full bg-blue-200 dark:bg-blue-900/50 rounded-full h-3 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-500 to-indigo-500 h-3 rounded-full transition-all duration-500 ease-out"
                            style="width: {{ ($invoiceProgress['total'] ?? 1) > 0 ? round((($invoiceProgress['current'] ?? 0) / ($invoiceProgress['total'] ?? 1)) * 100) : 0 }}%">
                        </div>
                    </div>
                </div>
                @endif

                <div wire:poll.keep-alive.10s class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700 transition-colors min-h-[450px] pb-32">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50/50 dark:bg-slate-900/40">
                            <tr class="bg-slate-50/50 dark:bg-slate-900/40">
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">NO. INVOICE</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">PELANGGAN</th>
                                <th class="hidden lg:table-cell px-6 py-4 text-left text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">PERIODE</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">TAGIHAN</th>
                                <th wire:click="sortBy('due_date')" class="hidden sm:table-cell cursor-pointer px-6 py-4 text-left text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest hover:bg-slate-100/50 dark:hover:bg-slate-900/60 transition-colors">
                                    <div class="flex items-center gap-1">
                                        TEMPO
                                        @if($sortField === 'due_date')
                                        @if($sortDirection === 'asc')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                        @else
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                        @endif
                                        @endif
                                    </div>
                                </th>
                                <th wire:click="sortBy('paid_at')" class="hidden md:table-cell cursor-pointer px-6 py-4 text-left text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest hover:bg-slate-100/50 dark:hover:bg-slate-900/60 transition-colors">
                                    <div class="flex items-center gap-1">
                                        TGL. BAYAR
                                        @if($sortField === 'paid_at')
                                        @if($sortDirection === 'asc')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                        @else
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                        @endif
                                        @endif
                                    </div>
                                </th>
                                <th wire:click="sortBy('status')" class="cursor-pointer px-6 py-4 text-left text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest hover:bg-slate-100/50 dark:hover:bg-slate-900/60 transition-colors">
                                    <div class="flex items-center gap-1">
                                        STATUS
                                        @if($sortField === 'status')
                                        @if($sortDirection === 'asc')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                        @else
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                        @endif
                                        @endif
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">AKSI</th>
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
                                <td class="hidden lg:table-cell px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-400">
                                    {{ $invoice->billing_period }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-900 dark:text-white">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                                        <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium">Uniq: +{{ $invoice->unique_code }}</span>
                                    </div>
                                </td>
                                <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-400">
                                    {{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}
                                </td>
                                <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-400">
                                    {{ $invoice->paid_at ? $invoice->paid_at->format('d/m/Y H:i') : '' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($invoice->status == 'unpaid')
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 uppercase tracking-widest">Unpaid</span>
                                    @elseif($invoice->status == 'paid')
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 uppercase tracking-widest">Paid</span>
                                    @else
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black bg-slate-100 dark:bg-slate-900/30 text-slate-700 dark:text-slate-400 uppercase tracking-widest">Canceled</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div x-data="{ 
                                            open: false, 
                                            pos: { top: 0, left: 0 },
                                            updatePos() {
                                                let rect = this.$refs.btn.getBoundingClientRect();
                                                this.pos.top = rect.bottom + window.scrollY;
                                                this.pos.left = rect.right - 192 + window.scrollX;
                                            }
                                        }" class="inline-block text-left">
                                        <button x-ref="btn" @click="open = !open; if(open) $nextTick(() => updatePos())"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-700/50 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl transition-all text-xs font-bold group">
                                            <span>Aksi</span>
                                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <template x-teleport="body">
                                            <div x-show="open" @click.away="open = false" x-cloak
                                                :style="`position: absolute; top: ${pos.top}px; left: ${pos.left}px;`"
                                                class="w-48 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 z-[9999] py-2 transition-all">

                                                <a href="{{ route('public.invoice', $invoice->id) }}" target="_blank" class="px-4 py-2 text-xs font-bold text-blue-600 dark:text-blue-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                    </svg>
                                                    Buka Invoice
                                                </a>
                                                <button wire:click="sendWhatsappNotification('{{ $invoice->id }}')" @click="open = false" class="w-full text-left px-4 py-2 text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.414 0 .018 5.394 0 12.03c0 2.119.552 4.188 1.598 6.049L0 24l6.104-1.602a11.834 11.834 0 005.937 1.57h.005c6.632 0 12.028-5.391 12.03-12.028a11.85 11.85 0 00-3.529-8.52" />
                                                    </svg>
                                                    Kirim WA
                                                </button>

                                                <div class="border-t border-slate-50 dark:border-slate-700 my-1"></div>
                                                @if($invoice->status == 'unpaid' && !$invoice->paid_at)
                                                <button wire:click="openVerifyModal('{{ $invoice->id }}')" @click="open = false" class="w-full text-left px-4 py-2 text-xs font-black text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    VERIFIKASI
                                                </button>

                                                @elseif($invoice->status == 'canceled')
                                                <button @click="
                                                            open = false;
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
                                                        " class="w-full text-left px-4 py-2 text-xs font-bold text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                    </svg>
                                                    GENERATE ULANG
                                                </button>
                                                @endif
                                                <button @click="
                                                        open = false;
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
                                                    " class="w-full text-left px-4 py-2 text-xs font-bold text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    HAPUS
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500 italic">Belum ada invoice untuk periode ini.</td>
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
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
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

                        @if(count($available_methods) > 0)
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Metode Pembayaran</label>
                            <select wire:model="selected_payment_method" class="w-full bg-slate-50 dark:bg-slate-900 dark:text-slate-200 border-none rounded-2xl px-5 py-4 focus:ring-2 focus:ring-blue-500 transition-all font-bold appearance-none">
                                <option value="">Pilih Metode (Opsional)</option>
                                @foreach($available_methods as $method)
                                <option value="{{ $method->nama }}">{{ $method->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <p class="text-sm text-slate-600 dark:text-slate-400 text-center mb-6">Pilih status penyelesaian untuk tagihan ini. Keduanya akan membuka akses internet pelanggan jika terisolir.</p>
                    </div>

                    <button wire:click="markAsPaid" class="w-full flex items-center justify-between p-4 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:hover:bg-emerald-900/30 border border-emerald-100 dark:border-emerald-800/30 rounded-2xl transition-all group">
                        <div class="text-left">
                            <p class="font-black text-emerald-700 dark:text-emerald-400">TANDAI LUNAS</p>
                            <p class="text-xs text-emerald-600/70">Pembayaran telah diterima tunai/transfer</p>
                        </div>
                        <svg class="w-6 h-6 text-emerald-500 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>

                    <button wire:click="markAsPiutang" class="w-full flex items-center justify-between p-4 bg-amber-50 hover:bg-amber-100 dark:bg-amber-900/20 dark:hover:bg-amber-900/30 border border-amber-100 dark:border-amber-800/30 rounded-2xl transition-all group">
                        <div class="text-left">
                            <p class="font-black text-amber-700 dark:text-amber-400">JADIKAN PIUTANG</p>
                            <p class="text-xs text-amber-600/70">Bayar nanti, internet tetap dibuka</p>
                        </div>
                        <svg class="w-6 h-6 text-amber-500 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
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
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                            <input
                                x-ref="searchInput"
                                wire:model.live.debounce.300ms="customerSearch"
                                @input="openSuggest = true"
                                type="text"
                                placeholder="Cari nama/ID/Username..."
                                class="w-full pl-10 bg-slate-50 dark:bg-slate-900 dark:text-slate-200 border-none rounded-2xl px-5 py-3.5 focus:ring-2 focus:ring-blue-500 transition-all font-bold text-sm shadow-inner"
                                autocomplete="off"
                                x-init="$watch('show', value => { if(value) setTimeout(() => $refs.searchInput.focus(), 100) })">

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
                                    <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    @else
                                    <svg class="w-5 h-5 text-slate-200 dark:text-slate-700 group-hover:text-blue-200" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                    </svg>
                                    @endif
                                </button>
                                @endforeach
                                @else
                                <div class="px-4 py-8 text-center" wire:loading.remove wire:target="customerSearch">
                                    <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
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
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="bg-amber-50 dark:bg-amber-900/20 p-4 rounded-2xl border border-amber-100 dark:border-amber-800/30">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
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
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-0 max-h-[60vh] overflow-y-auto">
                    <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-700">
                        <thead class="bg-slate-50/50 dark:bg-slate-900/40 sticky top-0 z-10 backdrop-blur-md">
                            <tr>
                                <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Pelanggan</th>
                                <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Periode & Tagihan</th>
                                <th class="px-8 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @foreach($customersToIsolate as $c)
                            @php
                            $unpaidInvoice = $c->invoices()->where('status', 'unpaid')->where('billing_period', now()->format('Y-m'))->first();
                            @endphp
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="whitespace-nowrap p-0">
                                    <a href="{{ route('customers.detail', $c->id) }}" class="block px-8 py-4 flex flex-col group transition-colors" wire:navigate>
                                        <span class="font-bold text-slate-900 dark:text-white group-hover:text-blue-600 transition-colors">{{ $c->name }}</span>
                                        <span class="text-[10px] text-slate-400 font-medium group-hover:text-blue-400 transition-colors">{{ $c->phone }}</span>
                                    </a>
                                </td>
                                <td class="px-8 py-4">
                                    @if($unpaidInvoice)
                                    <div class="flex flex-col">
                                        <span class="font-bold text-rose-600 dark:text-rose-400">Rp {{ number_format($unpaidInvoice->total_amount, 0, ',', '.') }}</span>
                                        <span class="text-[11px] text-slate-500 font-medium">Bulan: {{ \Carbon\Carbon::parse($unpaidInvoice->billing_period)->translatedFormat('F Y') }}</span>
                                    </div>
                                    @else
                                    <span class="text-slate-400 italic text-xs">Data tidak sinkron</span>
                                    @endif
                                </td>
                                <td class="px-8 py-4 text-right">
                                    @if($unpaidInvoice)
                                    <button wire:click="sendWhatsappNotification('{{ $unpaidInvoice->id }}')" class="inline-flex items-center gap-1.5 bg-emerald-500 hover:bg-emerald-600 text-white text-[10px] font-black px-4 py-2 rounded-xl shadow-sm transition-all uppercase">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.414 0 .018 5.394 0 12.03c0 2.119.552 4.188 1.598 6.049L0 24l6.104-1.602a11.834 11.834 0 005.937 1.57h.005c6.632 0 12.028-5.391 12.03-12.028a11.85 11.85 0 00-3.529-8.52" />
                                        </svg>
                                        Kirim WA
                                    </button>
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

    <!-- Phone Selection Modal -->
    <div x-data="{ show: @entangle('showPhoneSelectionModal') }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-[70] overflow-y-auto"
        style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="show = false; $wire.closePhoneSelectionModal()">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-8 py-6 border-b border-slate-50 dark:border-slate-700 text-center">
                    <div class="w-16 h-16 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.414 0 .018 5.394 0 12.03c0 2.119.552 4.188 1.598 6.049L0 24l6.104-1.602a11.834 11.834 0 005.937 1.57h.005c6.632 0 12.028-5.391 12.03-12.028a11.85 11.85 0 00-3.529-8.52" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Kirim WhatsApp</h3>
                    <p class="text-sm text-slate-500 mt-1">Pilih nomor tujuan pengiriman</p>
                </div>

                <div class="px-8 py-8 space-y-3">
                    @if(isset($customerPhones['phone']))
                    <button wire:click="confirmSendWhatsapp('{{ $customerPhones['phone'] }}')" class="w-full flex items-center justify-between p-4 bg-slate-50 hover:bg-blue-50 dark:bg-slate-900/40 dark:hover:bg-blue-900/20 border border-slate-100 dark:border-slate-700 rounded-2xl transition-all group">
                        <div class="text-left">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">WhatsApp Utama</p>
                            <p class="font-bold text-slate-700 dark:text-slate-200">{{ $customerPhones['phone'] }}</p>
                        </div>
                        <svg class="w-5 h-5 text-blue-500 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                    @endif

                    @if(isset($customerPhones['phone2']))
                    <button wire:click="confirmSendWhatsapp('{{ $customerPhones['phone2'] }}')" class="w-full flex items-center justify-between p-4 bg-slate-50 hover:bg-blue-50 dark:bg-slate-900/40 dark:hover:bg-blue-900/20 border border-slate-100 dark:border-slate-700 rounded-2xl transition-all group">
                        <div class="text-left">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">WhatsApp 2</p>
                            <p class="font-bold text-slate-700 dark:text-slate-200">{{ $customerPhones['phone2'] }}</p>
                        </div>
                        <svg class="w-5 h-5 text-blue-500 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                    @endif
                </div>

                <div class="px-8 py-4 bg-slate-50 dark:bg-slate-900/30 text-center border-t border-slate-50 dark:border-slate-700">
                    <button @click="show = false; $wire.closePhoneSelectionModal()" class="text-xs font-black text-slate-400 hover:text-slate-600 uppercase tracking-widest transition-all">Batal</button>
                </div>
            </div>
        </div>
    </div>
</div>