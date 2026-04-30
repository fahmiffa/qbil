<div class="max-w-2xl mx-auto px-4 py-8 sm:py-12">
    <!-- Header Branding -->
    <div class="text-center mb-10">
        @if($user->photo)
            <img src="{{ Storage::url($user->photo) }}" alt="{{ $user->name }}" class="w-20 h-20 rounded-full mx-auto mb-4 shadow-lg border-2 border-white dark:border-slate-800">
        @else
            <div class="w-20 h-20 bg-blue-600 rounded-full mx-auto mb-4 flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                {{ substr($user->name, 0, 1) }}
            </div>
        @endif
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $user->name }}</h1>
        <p class="text-slate-500 dark:text-slate-400">Order Voucher WiFi Online</p>
    </div>

    @if (session()->has('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    @if(!$showCheckout)
        <!-- Step 1: Selection & Input -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl shadow-blue-500/10 border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="p-6 sm:p-8 space-y-6">
                <!-- Package Selection -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-3">Pilih Paket Hotspot</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($packages as $package)
                            <label class="relative flex flex-col p-4 cursor-pointer rounded-2xl border-2 transition-all duration-200 {{ $selectedPackageId == $package->id ? 'border-blue-600 bg-blue-50/50 dark:bg-blue-900/20' : 'border-slate-100 dark:border-slate-800 hover:border-blue-300 dark:hover:border-slate-700' }}">
                                <input type="radio" wire:model.live="selectedPackageId" value="{{ $package->id }}" class="sr-only">
                                <span class="text-lg font-bold text-slate-900 dark:text-white">{{ $package->name }}</span>
                                <span class="text-blue-600 dark:text-blue-400 font-extrabold mt-1">Rp {{ number_format($package->price, 0, ',', '.') }}</span>
                                <div class="mt-2 flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    {{ $package->speed_upload }} / {{ $package->speed_download }}
                                </div>
                                @if($selectedPackageId == $package->id)
                                    <div class="absolute top-3 right-3 text-blue-600">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                    </div>
                                @endif
                            </label>
                        @endforeach
                    </div>
                    @error('selectedPackageId') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- WhatsApp Input -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nomor WhatsApp</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <span class="text-sm font-bold">+62</span>
                        </div>
                        <input type="text" wire:model="whatsapp" placeholder="8123456789" class="w-full pl-12 pr-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl focus:ring-2 focus:ring-blue-600 dark:text-white transition-all text-lg font-medium">
                    </div>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400 italic">Voucher akan dikirim otomatis ke nomor ini.</p>
                    @error('whatsapp') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Quantity -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Jumlah Voucher</label>
                    <div class="flex items-center gap-4">
                        <button type="button" wire:click="$set('quantity', Math.max(1, quantity - 1))" class="w-12 h-12 flex items-center justify-center bg-slate-100 dark:bg-slate-800 rounded-xl hover:bg-slate-200 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                        </button>
                        <input type="number" wire:model.live="quantity" class="w-20 text-center py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl focus:ring-2 focus:ring-blue-600 dark:text-white text-lg font-bold">
                        <button type="button" wire:click="$set('quantity', quantity + 1)" class="w-12 h-12 flex items-center justify-center bg-slate-100 dark:bg-slate-800 rounded-xl hover:bg-slate-200 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </button>
                    </div>
                    @error('quantity') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Summary Footer -->
            <div class="bg-slate-50 dark:bg-slate-900/50 p-6 sm:p-8 border-t border-slate-100 dark:border-slate-800">
                <div class="flex items-center justify-between mb-6">
                    <span class="text-slate-500 dark:text-slate-400 font-medium text-lg">Total Bayar</span>
                    <span class="text-3xl font-black text-blue-600 dark:text-blue-400">Rp {{ number_format($total_amount, 0, ',', '.') }}</span>
                </div>
                <button wire:click="checkout" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-4 rounded-2xl shadow-xl shadow-blue-500/20 transition-all flex items-center justify-center gap-3 text-lg group">
                    Checkout Sekarang
                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </div>
        </div>
    @else
        <!-- Step 2: QRIS Checkout -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl shadow-blue-500/10 border border-slate-100 dark:border-slate-800 overflow-hidden text-center">
            <div class="p-6 sm:p-8">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Pembayaran QRIS</h2>
                <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">Scan QRIS di bawah untuk menyelesaikan pesanan</p>

                <!-- QRIS Canvas -->
                <div class="bg-white p-4 rounded-2xl inline-block shadow-inner mb-6">
                    @if($qris_payload)
                        {!! QrCode::size(250)->generate($qris_payload) !!}
                    @else
                        <div class="w-[250px] h-[250px] bg-slate-100 animate-pulse rounded-lg flex items-center justify-center text-slate-400 italic">
                            Gagal memuat QRIS
                        </div>
                    @endif
                </div>

                <div class="space-y-4 text-left bg-slate-50 dark:bg-slate-800/50 p-5 rounded-2xl border border-slate-100 dark:border-slate-800">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-500 dark:text-slate-400">Merchant</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ \App\Services\QrisLogic::parseMerchantName($user->appSetting->qr ?? '') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-500 dark:text-slate-400">Total Tagihan</span>
                        <span class="font-black text-blue-600 dark:text-blue-400 text-lg">Rp {{ number_format($total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="mt-8 space-y-3">
                    <p class="text-xs text-slate-400 uppercase tracking-widest font-bold mb-4 italic">Instruksi Pembayaran</p>
                    <ul class="text-xs text-slate-500 dark:text-slate-400 text-left space-y-2 list-disc pl-4">
                        <li>Buka aplikasi pembayaran (Gopay, OVO, Dana, LinkAja, atau Mobile Banking).</li>
                        <li>Scan kode QR di atas.</li>
                        <li>Pastikan jumlah pembayaran sesuai.</li>
                        <li>Klik Bayar. Voucher akan dikirim via WhatsApp setelah verifikasi (Manual/Auto).</li>
                    </ul>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800">
                    <button wire:click="back" class="text-slate-500 dark:text-slate-400 font-bold hover:text-slate-700 dark:hover:text-white transition-colors flex items-center justify-center gap-2 mx-auto">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Ganti Pesanan
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Footer Security -->
    <div class="mt-10 text-center flex flex-col items-center gap-4">
        <div class="flex items-center gap-4 opacity-30 grayscale contrast-125">
            <img src="https://upload.wikimedia.org/wikipedia/commons/a/a2/Logo_QRIS.svg" alt="QRIS" class="h-6">
            <img src="https://upload.wikimedia.org/wikipedia/commons/e/eb/GPN_Logo.svg" alt="GPN" class="h-6">
        </div>
        <p class="text-xs text-slate-400 font-medium">&copy; {{ date('Y') }} {{ config('app.name') }}. Secured by {{ $user->name }} Infrastructure.</p>
    </div>
</div>
