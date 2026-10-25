<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('WhatsApp') }}
        </h2>
    </x-slot>

    <div class="py-12" wire:poll.10s="refreshStatusAndQr">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <!-- Main Card -->
            <div class="bg-white dark:bg-slate-800 shadow-xl rounded-2xl p-8 text-center border border-gray-100 dark:border-slate-700 overflow-hidden relative transition-colors"
                 x-data="{
                     countdown: 10,
                     timer: null,
                     init() {
                         this.startTimer();
                         $wire.watch('lastRefreshedAt', () => {
                             this.resetTimer();
                         });
                     },
                     startTimer() {
                         if (this.timer) clearInterval(this.timer);
                         this.countdown = 10;
                         this.timer = setInterval(() => {
                             if (this.countdown > 1) {
                                 this.countdown--;
                             } else {
                                 this.countdown = 10;
                             }
                         }, 1000);
                     },
                     resetTimer() {
                         this.countdown = 10;
                     }
                 }">

                <!-- Icon & Title -->
                <div class="flex flex-col items-center gap-4 mb-8 mt-2">
                    <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-2xl shadow-sm border border-emerald-100 dark:border-emerald-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="5" height="5" x="3" y="3" rx="1"/>
                            <rect width="5" height="5" x="16" y="3" rx="1"/>
                            <rect width="5" height="5" x="3" y="16" rx="1"/>
                            <path d="M21 16h-3a2 2 0 0 0-2 2v3"/>
                            <path d="M21 21v.01"/>
                            <path d="M12 7v3a2 2 0 0 1-2 2H7"/>
                            <path d="M3 12h.01"/>
                            <path d="M12 3h.01"/>
                            <path d="M12 16h.01"/>
                            <path d="M16 12h1"/>
                            <path d="M21 12v.01"/>
                            <path d="M12 21v-1"/>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Scan WhatsApp</h1>
                    <p class="text-gray-500 dark:text-slate-400 text-sm">Hubungkan akun WhatsApp Anda untuk pengiriman notifikasi otomatis.</p>
                </div>

                <!-- Device ID / Session Info -->
                <div class="text-left mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-gray-700 dark:text-slate-300 text-xs font-bold uppercase tracking-widest">
                            Device ID / Nomor WhatsApp
                        </label>
                        @if($isConnected && $isLoggedIn)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                                Terhubung
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                Belum Terhubung
                            </span>
                        @endif
                    </div>
                    <div class="relative">
                        <input type="text" 
                               readonly 
                               value="{{ $deviceId ?: 'Menunggu device ID...' }}"
                               class="w-full bg-gray-50 dark:bg-slate-900 dark:text-slate-300 border border-gray-200 dark:border-slate-700 text-gray-700 rounded-xl px-4 py-3.5 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all font-mono text-center text-lg font-bold"
                               placeholder="Nomor belum diatur">
                    </div>
                </div>

                <!-- QR / Status Display Area -->
                <div class="relative flex flex-col justify-center items-center bg-gray-50 dark:bg-slate-900/50 border-2 border-dashed border-gray-200 dark:border-slate-700 p-8 rounded-2xl min-h-[360px] transition-all duration-500">
                    
                    @if($isConnected && $isLoggedIn)
                        <!-- Connected State -->
                        <div class="text-green-600 dark:text-green-400 font-bold flex flex-col items-center gap-4 py-8 animate-fadeIn">
                            <div class="bg-green-100 dark:bg-green-900/40 p-5 rounded-full shadow-inner border border-green-200 dark:border-green-800">
                                <svg class="w-20 h-20 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-black text-gray-900 dark:text-white">WhatsApp Terhubung!</p>
                                <p class="text-green-600 dark:text-green-400 font-medium text-sm mt-1">Sistem siap mengirimkan pesan otomatis.</p>
                                @if($jid)
                                    <p class="text-xs text-gray-400 dark:text-slate-500 mt-2 font-mono">{{ $jid }}</p>
                                @endif
                            </div>
                        </div>

                    @elseif($qr)
                        <!-- QR Code Ready to Scan -->
                        <div class="flex flex-col items-center gap-4">
                            <!-- Countdown & Auto-Refresh Indicator -->
                            <div class="flex items-center gap-2 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-3.5 py-1.5 rounded-full text-xs font-semibold border border-blue-200 dark:border-blue-800">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-600"></span>
                                </span>
                                <span>Auto refresh QR dalam <span class="font-mono font-bold" x-text="countdown">10</span> detik</span>
                            </div>

                            <p class="text-gray-700 dark:text-slate-200 font-bold text-sm">
                                Buka WhatsApp &gt; Perangkat Tertaut &gt; Tautkan Perangkat
                            </p>

                            <!-- QR Image Container -->
                            <div class="bg-white p-4 rounded-xl shadow-lg border border-gray-100 dark:border-slate-700 transform transition-transform hover:scale-105 duration-300">
                                <img src="{{ $qr }}" 
                                     alt="WhatsApp QR Code" 
                                     class="max-w-full h-[250px] w-[250px] object-contain">
                            </div>

                            <p class="text-gray-400 dark:text-slate-400 text-xs italic">
                                Scan QR code di atas menggunakan aplikasi WhatsApp Anda.
                            </p>
                        </div>

                    @else
                        <!-- Loading State -->
                        <div class="flex flex-col items-center gap-4 py-8">
                            <div class="animate-spin rounded-full h-12 w-12 border-4 border-gray-200 dark:border-slate-700 border-t-emerald-600"></div>
                            <p class="text-gray-500 dark:text-slate-400 text-sm font-medium animate-pulse">
                                {{ $status }}
                            </p>
                        </div>
                    @endif

                </div>

                <!-- Bottom Action & Info -->
                <div class="mt-8 flex flex-col gap-3">
                    <button wire:click="refreshStatusAndQr" 
                            wire:loading.attr="disabled"
                            class="w-full bg-gray-900 dark:bg-slate-700 hover:bg-black dark:hover:bg-slate-600 text-white font-bold py-3.5 px-4 rounded-xl transition-all shadow-lg hover:shadow-xl active:scale-95 flex items-center justify-center gap-2 disabled:opacity-50">
                        <svg wire:loading.remove class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <svg wire:loading class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove>MUAT ULANG KONEKSI</span>
                        <span wire:loading>MEMPERBARUI STATUS...</span>
                    </button>

                    <div class="flex items-center justify-between text-xs text-gray-400 dark:text-slate-500 px-1">
                        <span>Polling otomatis setiap 10 detik</span>
                        @if($lastRefreshedAt)
                            <span>Pembaruan: <span class="font-mono font-medium text-gray-600 dark:text-slate-400">{{ $lastRefreshedAt }}</span></span>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
