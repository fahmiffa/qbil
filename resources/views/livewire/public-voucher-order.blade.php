<div class="min-h-screen bg-[#F8FAFC] dark:bg-[#0F172A] py-8 px-4 flex flex-col items-center">
    <div class="w-full max-w-md space-y-8">

        @if(!$showCheckout)
        <!-- Header (Minimalist & Circle) -->
        <div class="text-center animate-fade-in-down pt-4">
            <div class="relative inline-block group">
                <div class="absolute inset-0 bg-blue-600 rounded-full blur-2xl opacity-20 group-hover:opacity-40 transition-opacity"></div>
                @if($user->photo)
                <img src="{{ Storage::url($user->photo) }}" alt="{{ $user->name }}" class="relative w-24 h-24 rounded-full mx-auto shadow-2xl border-4 border-white dark:border-slate-800 object-cover">
                @else
                <div class="relative w-24 h-24 bg-gradient-to-br from-indigo-600 to-blue-500 rounded-full mx-auto flex items-center justify-center text-white text-3xl font-black shadow-2xl border-4 border-white dark:border-slate-800">
                    {{ substr($user->name, 0, 1) }}
                </div>
                @endif
            </div>
            <h1 class="mt-6 text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">{{ $user->name }}</h1>
            <p class="text-slate-500 dark:text-slate-400 font-bold uppercase tracking-[0.3em] text-[10px] mt-1">Order Voucher WiFi</p>
        </div>

        <!-- Package Selection (Horizontal Scroll) -->
        <div class="space-y-4 pt-4">
            <div class="flex items-center justify-between px-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Pilih Paket</label>
                <span class="text-[9px] font-black text-blue-500 uppercase tracking-widest animate-pulse">Swipe &rsaquo;</span>
            </div>

            <div class="flex overflow-x-auto pb-6 gap-4 no-scrollbar snap-x">
                @foreach($packages as $package)
                <div wire:click="$set('selectedPackageId', {{ $package->id }})"
                    class="flex-shrink-0 w-44 snap-center relative p-5 rounded-[2.5rem] border-2 transition-all duration-300 cursor-pointer {{ $selectedPackageId == $package->id ? 'border-blue-600 bg-white dark:bg-slate-800 shadow-xl shadow-blue-600/10' : 'border-white dark:border-slate-800/50 bg-white dark:bg-slate-900 shadow-sm' }}">

                    <div class="w-10 h-10 bg-gradient-to-br {{ $selectedPackageId == $package->id ? 'from-blue-600 to-indigo-600' : 'from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-700' }} rounded-2xl flex items-center justify-center text-white mb-4 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>

                    <h3 class="text-sm font-black text-slate-900 dark:text-white leading-tight uppercase tracking-tight truncate">{{ $package->name }}</h3>
                    <p class="text-[10px] font-bold text-slate-400 mb-2">Up to {{ $package->speed_download }}</p>
                    <span class="text-base font-black text-blue-600 dark:text-blue-400 tracking-tighter">Rp {{ number_format($package->price, 0, ',', '.') }}</span>

                    @if($selectedPackageId == $package->id)
                    <div class="absolute top-4 right-4 bg-blue-600 text-white p-1 rounded-full shadow-lg scale-90">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"></path>
                        </svg>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        <!-- Main Input Card (Mobile Focused) -->
        <div class="bg-white dark:bg-slate-900 rounded-[3rem] shadow-xl border border-white dark:border-slate-800 overflow-hidden relative animate-fade-in">
            <div class="p-8 space-y-8 relative z-10">
                <!-- WhatsApp -->
                <div class="space-y-3">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-2">WhatsApp (Format 08)</label>
                    <input type="text" wire:model.live.debounce.500ms="whatsapp"
                        placeholder="081234567890"
                        class="w-full px-7 py-5 bg-slate-50 dark:bg-slate-800/50 border-2 border-transparent focus:border-blue-600 focus:bg-white dark:focus:bg-slate-800 rounded-[2rem] focus:ring-0 dark:text-white transition-all text-xl font-black tracking-wider placeholder:text-slate-300 dark:placeholder:text-slate-700 shadow-inner">
                    <div class="flex justify-between items-center pl-4 pr-2">
                        @error('whatsapp') <span class="text-red-500 text-[9px] block font-black uppercase tracking-widest">{{ $message }}</span> @enderror
                        @if($is_member)
                        <span class="text-emerald-500 text-[9px] block font-black uppercase tracking-widest">Member Verified ✓</span>
                        @endif
                    </div>
                </div>

                <!-- Quantity -->
                <div class="space-y-3">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-2">Jumlah</label>
                    <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-800/50 p-2 rounded-[2rem] shadow-inner">
                        <button type="button" wire:click="decrement" class="w-12 h-12 flex items-center justify-center bg-white dark:bg-slate-700 rounded-full shadow-md active:scale-90 transition-all text-slate-900 dark:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M20 12H4"></path>
                            </svg>
                        </button>
                        <span class="text-2xl font-black text-slate-900 dark:text-white">{{ $quantity }}</span>
                        <button type="button" wire:click="increment" class="w-12 h-12 flex items-center justify-center bg-white dark:bg-slate-700 rounded-full shadow-md active:scale-90 transition-all text-slate-900 dark:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Total & Checkout -->
                <div class="pt-6 border-t border-slate-50 dark:border-slate-800">
                    @if($applied_discount_name)
                    <div class="flex justify-between items-center mb-3 bg-blue-50 dark:bg-blue-900/20 p-3 rounded-2xl border border-blue-100 dark:border-blue-800/30">
                        <div>
                            <span class="text-[9px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest block">{{ $applied_discount_name }}</span>
                            <span class="text-xs font-bold text-slate-500">Harga Awal: Rp {{ number_format($selectedPackage?->price * $quantity, 0, ',', '.') }}</span>
                        </div>
                        <span class="text-sm font-black text-red-500">- Rp {{ number_format($discount_amount, 0, ',', '.') }}</span>
                    </div>
                    @elseif($is_member && $min_quota_for_discount > 0 && $quantity < $min_quota_for_discount)
                        <div class="mb-3 bg-orange-50 dark:bg-orange-900/20 p-3 rounded-2xl border border-orange-100 dark:border-orange-800/30 flex items-center gap-2">
                        <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-[9px] font-black text-orange-600 dark:text-orange-400 uppercase tracking-widest">Beli minimal {{ $min_quota_for_discount }} voucher untuk dapat diskon!</span>
                </div>
                @endif

                <div class="flex justify-between items-end mb-6">
                    <div>
                        <p class="text-slate-400 text-[9px] font-black uppercase tracking-widest mb-1">Total Bayar</p>
                        <h4 class="text-3xl font-black text-blue-600 tracking-tighter leading-none">Rp {{ number_format($total_amount, 0, ',', '.') }}</h4>
                    </div>
                    <span class="text-[9px] bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full font-black uppercase italic shadow-sm">Verified</span>
                </div>
                <button wire:click="checkout" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-5 rounded-[2rem] shadow-xl shadow-blue-600/20 transition-all flex items-center justify-center gap-4 text-xl group active:scale-[0.98]">
                    Checkout
                    <svg class="w-6 h-6 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    @else
    <!-- Step 2: Mobile Focused Checkout -->
    <div class="bg-white dark:bg-slate-900 rounded-[3rem] shadow-xl border border-white dark:border-slate-800 overflow-hidden text-center animate-fade-in">
        <div class="p-8 sm:p-10">
            <div class="mb-4 inline-flex items-center gap-2 px-3 py-1 bg-emerald-500/10 text-emerald-600 rounded-full text-[9px] font-black uppercase tracking-widest border border-emerald-500/20">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                Secure Payment
            </div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-8 tracking-tight uppercase tracking-wider">Aktivasi QRIS</h2>

            <!-- QR Wrapper -->
            <div class="relative inline-block mb-10 p-6 bg-white rounded-[3rem] shadow-2xl border border-slate-100">
                @if($qris_payload)
                {!! QrCode::size(240)->generate($qris_payload) !!}
                @else
                <div class="w-[240px] h-[240px] flex items-center justify-center">
                    <div class="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                </div>
                @endif
            </div>

            <!-- Compact Bill -->
            <div class="bg-blue-600 rounded-[2.5rem] p-6 text-white text-left shadow-lg mb-8 relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-6 -mr-6 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                <div class="relative z-10 space-y-3">
                    <div class="flex justify-between items-center opacity-70">
                        <span class="text-[9px] font-black uppercase tracking-widest">Order ID</span>
                        <span class="text-xs font-black">{{ $orderCode }}</span>
                    </div>
                    <div class="h-px bg-white/10"></div>
                    <div class="flex justify-between items-end">
                        <div class="flex flex-col">
                            <span class="text-[9px] font-black uppercase tracking-widest opacity-60">Total</span>
                            <span class="text-3xl font-black tracking-tighter leading-none">Rp {{ number_format($final_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="text-[10px] font-black uppercase opacity-40">Verified</div>
                    </div>
                </div>
            </div>

            <!-- Short Instructions -->
            <div class="space-y-3 text-left mb-10">
                <div class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl">
                    <span class="w-8 h-8 rounded-xl bg-white dark:bg-slate-700 flex items-center justify-center text-[10px] font-black">01</span>
                    <p class="text-[10px] text-slate-500 font-bold">Screenshot & bayar dengan E-Wallet Anda.</p>
                </div>
                <div class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl">
                    <span class="w-8 h-8 rounded-xl bg-white dark:bg-slate-700 flex items-center justify-center text-[10px] font-black">02</span>
                    <p class="text-[10px] text-slate-500 font-bold">Voucher otomatis dikirim via WhatsApp.</p>
                </div>
            </div>

            <button wire:click="back" class="text-slate-400 hover:text-blue-600 font-black text-[9px] uppercase tracking-[0.2em] flex items-center justify-center gap-2 mx-auto transition-all active:scale-95 group">
                <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Ganti Pesanan
            </button>
        </div>
    </div>
    @endif

    <!-- Minimal Footer -->
    <div class="mt-8 text-center opacity-30">
        <p class="text-[8px] text-slate-400 font-black uppercase tracking-[0.5em]">&copy; {{ date('Y') }} Secured by {{ $user->name }}</p>
    </div>
</div>

<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    @keyframes fade-in-down {
        0% {
            opacity: 0;
            transform: translateY(-20px);
        }

        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fade-in {
        0% {
            opacity: 0;
        }

        100% {
            opacity: 1;
        }
    }

    .animate-fade-in-down {
        animation: fade-in-down 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .animate-fade-in {
        animation: fade-in 1s ease-out;
    }
</style>
</div>