<div>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-slate-800 dark:text-white tracking-tight">Manajemen Deposit Pelanggan</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-2xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Total Deposit Aktif</p>
                            <h3 class="text-2xl font-black text-slate-800 dark:text-white">
                                {{ \App\Models\Deposit::where('status', 'active')->count() }} 
                                <span class="text-sm font-medium text-slate-400 ml-1">Transaksi</span>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Listing -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-50 dark:border-slate-700 flex flex-wrap items-center justify-between gap-4">
                    <button wire:click="create" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold py-2.5 px-6 rounded-xl transition-all shadow-[0_8px_20px_-4px_rgba(37,99,235,0.4)] hover:shadow-[0_12px_24px_-6px_rgba(37,99,235,0.5)] transform hover:-translate-y-0.5 active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Catat Deposit Baru
                    </button>
                    <div class="relative w-full sm:w-80">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari Nama Pelanggan..." 
                            class="w-full pl-11 bg-slate-50 dark:bg-slate-900/50 border-none rounded-2xl py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all dark:text-white">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-900/50 text-left">
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Pelanggan</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Detail Deposit</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Nominal Total</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Progress</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-widest">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-700">
                            @forelse($deposits as $deposit)
                                <tr wire:key="deposit-{{ $deposit->id }}" class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center font-black text-sm">
                                                {{ substr($deposit->customer->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-800 dark:text-white">{{ $deposit->customer->name }}</p>
                                                <p class="text-xs text-slate-400">{{ $deposit->customer->id_pelanggan }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-sm text-slate-600 dark:text-slate-400">
                                        <div class="font-bold text-slate-800 dark:text-slate-200">{{ $deposit->months_count }} Bulan</div>
                                        <div class="text-[11px] opacity-70 italic">{{ $deposit->package->name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <p class="font-black text-slate-800 dark:text-white">Rp {{ number_format($deposit->total_amount, 0, ',', '.') }}</p>
                                        <p class="text-[10px] text-slate-400">@ Rp {{ number_format($deposit->amount_per_month, 0, ',', '.') }}</p>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                                <div class="h-full bg-blue-500" style="width: {{ ($deposit->used_months / $deposit->months_count) * 100 }}%"></div>
                                            </div>
                                            <span class="text-[11px] font-bold text-slate-500">{{ $deposit->used_months }}/{{ $deposit->months_count }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        @if($deposit->status == 'active')
                                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Aktif</span>
                                        @elseif($deposit->status == 'exhausted')
                                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400">Habis</span>
                                        @else
                                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Batal</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5 text-right space-x-2 whitespace-nowrap">
                                        <a href="{{ route('print.deposit', $deposit->id) }}" target="_blank" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 font-bold text-xs uppercase tracking-widest bg-blue-50 dark:bg-blue-900/30 px-3 py-1.5 rounded-lg transition-all inline-block">Cetak</a>
                                        <button wire:click="edit('{{ $deposit->id }}')" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 font-bold text-xs uppercase tracking-widest bg-indigo-50 dark:bg-indigo-900/30 px-3 py-1.5 rounded-lg transition-all">Edit</button>
                                        <button @click="Swal.fire({
                                            title: 'Hapus Deposit?',
                                            text: 'Data deposit ini akan dihapus permanen!',
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonColor: '#ef4444',
                                            cancelButtonColor: '#64748b',
                                            confirmButtonText: 'Hapus',
                                            background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff',
                                            color: document.documentElement.classList.contains('dark') ? '#f1f5f9' : '#0f172a'
                                        }).then((result) => { if (result.isConfirmed) { $wire.delete('{{ $deposit->id }}') } })" 
                                            class="text-red-500 hover:text-red-700 font-bold text-xs uppercase tracking-widest">Hapus</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-400 italic">Belum ada data deposit.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-6">
                    {{ $deposits->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div x-data="{ show: @entangle('isOpen') }" 
         x-show="show" 
         x-cloak
         class="fixed inset-0 z-[60] flex items-center justify-center p-4"
         style="display: none;">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
             @click="show = false; $wire.closeModal()"></div>

        <!-- Modal Container -->
        <div wire:key="deposit-modal-content" 
             class="relative bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-2xl transform transition-all w-full max-w-xl flex flex-col border border-slate-200 dark:border-slate-800 overflow-hidden">
            
            <form wire:submit.prevent="store" class="flex flex-col max-h-[85vh]">
                <!-- Header -->
                <div class="px-8 py-6 border-b border-slate-50 dark:border-slate-800 flex-shrink-0">
                    <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">
                        {{ $deposit_id ? 'Detail / Edit Deposit' : 'Input Pembayaran Deposit' }}
                    </h3>
                </div>

                <!-- Body (Scrollable) -->
                <div class="px-8 py-6 space-y-6 overflow-y-auto custom-scrollbar flex-1">
                    <!-- Pilih Pelanggan - Search Dropdown Component -->
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Pilih Pelanggan</label>
                        
                        <!-- Trigger Button -->
                        <button type="button" @click="open = !open" 
                            class="w-full flex items-center justify-between bg-slate-50 dark:bg-slate-950 border-2 border-transparent hover:border-blue-500/30 rounded-2xl px-4 py-3 text-sm transition-all shadow-inner group">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <span class="font-bold {{ $selected_customer_name ? 'text-slate-800 dark:text-slate-100' : 'text-slate-400 dark:text-slate-500' }}">
                                    {{ $selected_customer_name ?: 'Klik untuk mencari pelanggan...' }}
                                </span>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-500 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <!-- Dropdown Panel -->
                        <div x-show="open" 
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="absolute z-[70] w-full mt-2 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 overflow-hidden">
                            
                            <!-- Search Input inside Dropdown -->
                            <div class="p-3 border-b border-slate-50 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50">
                                <div class="relative">
                                    <input type="text" 
                                        x-ref="searchInput"
                                        wire:model.live.debounce.300ms="customer_search"
                                        placeholder="Ketik nama pelanggan..."
                                        class="w-full bg-white dark:bg-slate-800 border-none rounded-xl pl-10 pr-4 py-2 text-xs focus:ring-2 focus:ring-blue-500 outline-none transition-all dark:text-white font-bold shadow-sm">
                                    <svg class="w-4 h-4 absolute left-3 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                            </div>

                            <!-- Results List -->
                            <div class="max-h-52 overflow-y-auto">
                                @if(count($customers_list) > 0)
                                    @foreach($customers_list as $cust)
                                        <button type="button" 
                                            wire:key="opt-cust-{{ $cust->id }}"
                                            wire:click="selectCustomer('{{ $cust->id }}', '{{ $cust->name }}', {{ $cust->package->price ?? 0 }})"
                                            @click="open = false"
                                            class="w-full px-5 py-3 text-left hover:bg-blue-50 dark:hover:bg-blue-900/10 flex items-center justify-between group transition-colors border-b last:border-0 border-slate-50 dark:border-slate-700">
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-black text-slate-800 dark:text-white group-hover:text-blue-600 transition-colors">
                                                    {{ $cust->name }} 
                                                    <span class="text-[11px] text-slate-400 font-bold ml-1">({{ $cust->package->name ?? 'No Pkg' }}, Rp {{ number_format($cust->package->price ?? 0, 0, ',', '.') }})</span>
                                                </p>
                                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ $cust->id_pelanggan }}</p>
                                            </div>
                                            <div class="opacity-0 group-hover:opacity-100 transition-opacity text-blue-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                        </button>
                                    @endforeach
                                @else
                                    <div class="px-5 py-8 text-center">
                                        <p class="text-xs font-bold text-slate-400">Silahkan ketik nama pelanggan...</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @error('customer_id') <p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p> @enderror
                    </div>

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
                    <button type="submit" 
                        class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-black rounded-2xl transition-all shadow-lg shadow-blue-600/20 uppercase tracking-widest">
                        Simpan Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
