<div class="p-6 space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Manajemen Pelanggan & Voucher</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Kelola pesanan voucher dan data member publik</p>
        </div>
    </div>

    @if (session()->has('message'))
    <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 border-2 border-emerald-100 dark:border-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-2xl flex items-center gap-3 shadow-lg shadow-emerald-500/5">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
        </svg>
        <span class="text-xs font-black uppercase tracking-tight">{{ session('message') }}</span>
    </div>
    @endif

    <!-- Tabs -->
    <div class="flex items-center gap-4 border-b border-slate-200 dark:border-slate-800 pb-2">
        <button wire:click="$set('activeTab', 'vouchers')" class="px-4 py-2 text-sm font-black uppercase tracking-widest transition-colors {{ $activeTab === 'vouchers' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300' }}">Voucher</button>
        <button wire:click="$set('activeTab', 'members')" class="px-4 py-2 text-sm font-black uppercase tracking-widest transition-colors {{ $activeTab === 'members' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300' }}">Member</button>
        <button wire:click="$set('activeTab', 'discounts')" class="px-4 py-2 text-sm font-black uppercase tracking-widest transition-colors {{ $activeTab === 'discounts' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300' }}">Diskon</button>
    </div>

    @if($activeTab === 'vouchers')
    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Pesanan</p>
            <h3 class="text-3xl font-black text-slate-900 dark:text-white tracking-tighter">{{ $orders->total() }}</h3>
        </div>
        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Menunggu Pembayaran</p>
            <h3 class="text-3xl font-black text-orange-500 tracking-tighter">{{ \App\Models\VoucherOrder::where('user_id', auth()->id())->where('payment_status', 'unpaid')->count() }}</h3>
        </div>
        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Lunas</p>
            <h3 class="text-3xl font-black text-emerald-500 tracking-tighter">{{ \App\Models\VoucherOrder::where('user_id', auth()->id())->where('payment_status', 'paid')->count() }}</h3>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-50 dark:border-slate-800/50">
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Order Code</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">WhatsApp</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Paket / Jml</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Total Bayar</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                    @forelse($orders as $order)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors group cursor-pointer" wire:click="toggleVouchers({{ $order->id }})">
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-black text-slate-900 dark:text-white tracking-tight group-hover:text-blue-600 transition-colors">{{ $order->order_code }}</span>
                                @if($order->hotspotUsers->count() > 0)
                                <svg class="w-4 h-4 text-slate-300 transform transition-transform {{ $selectedOrderId == $order->id ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path>
                                </svg>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <a href="https://wa.me/62{{ ltrim($order->whatsapp, '0') }}" target="_blank" class="inline-flex items-center gap-2 text-sm font-bold text-blue-600 hover:underline" wire:click.stop>
                                {{ $order->whatsapp }}
                            </a>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex flex-col">
                                <span class="text-sm font-black text-slate-700 dark:text-slate-300">{{ $order->package->name ?? 'N/A' }}</span>
                                <span class="text-[10px] font-bold text-slate-400">{{ $order->quantity }} Voucher</span>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <span class="text-sm font-black text-emerald-600 dark:text-emerald-400">Rp {{ number_format($order->total_price + $order->unique_amount, 0, ',', '.') }}</span>
                        </td>
                        <td class="px-6 py-5">
                            @if($order->payment_status === 'paid')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">Lunas</span>
                            @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400 border border-orange-200 dark:border-orange-800">Menunggu</span>
                            @endif
                        </td>
                        <td class="px-6 py-5" wire:click.stop>
                            @if($order->payment_status !== 'paid')
                            <button wire:click="requestMarkAsPaid({{ $order->id }})" class="bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-black px-4 py-2 rounded-xl transition-all shadow-lg shadow-blue-600/20 uppercase tracking-widest active:scale-95">
                                Verifikasi
                            </button>
                            @else
                            <div class="flex items-center gap-3">
                                <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest italic">Selesai</span>
                                <button wire:click="sendManualWhatsapp({{ $order->id }})" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-600 border border-emerald-200 text-[10px] font-black px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 uppercase tracking-widest dark:bg-emerald-900/30 dark:border-emerald-800 dark:hover:bg-emerald-900/50" title="Kirim Pesan WhatsApp">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.418-.099.823z" />
                                    </svg>
                                    Kirim WA
                                </button>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @if($selectedOrderId == $order->id)
                    <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                        <td colspan="6" class="px-8 py-6">
                            @if($order->hotspotUsers->count() > 0)
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
                                @foreach($order->hotspotUsers as $voucher)
                                <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-800 shadow-sm relative group overflow-hidden">
                                    <div class="absolute top-0 left-0 w-1 h-full bg-blue-600"></div>
                                    <div class="flex flex-col gap-2">
                                        <div class="flex flex-col">
                                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Username</span>
                                            <span class="text-sm font-black text-slate-900 dark:text-white">{{ $voucher->username }}</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Password</span>
                                            <span class="text-sm font-black text-slate-900 dark:text-white">{{ $voucher->password }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="flex items-center gap-3 text-slate-400 italic text-[10px] font-bold uppercase tracking-widest">
                                <div class="w-4 h-4 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                                Sedang memproses voucher... Refresh halaman sebentar lagi.
                            </div>
                            @endif
                        </td>
                    </tr>
                    @endif
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center">
                            <p class="text-slate-500 font-bold uppercase tracking-widest text-[10px]">Belum ada pesanan</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-50 dark:border-slate-800/50">
            {{ $orders->links() }}
        </div>
    </div>
    @elseif($activeTab === 'members')
    <livewire:member-manager />
    @elseif($activeTab === 'discounts')
    <livewire:discount-manager />
    @endif

    <script>
        window.addEventListener('swal:confirm', event => {
            const data = event.detail[0];
            Swal.fire({
                title: data.title,
                text: data.text,
                icon: data.type,
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Verifikasi!',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'rounded-[2rem]',
                    confirmButton: 'rounded-xl font-black uppercase tracking-widest text-[10px] px-6 py-3',
                    cancelButton: 'rounded-xl font-black uppercase tracking-widest text-[10px] px-6 py-3'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch(data.callback, {
                        orderId: data.id
                    });
                }
            });
        });
    </script>
</div>