<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('WhatsApp') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <!-- Main Card -->
            <div class="bg-white dark:bg-slate-800 shadow-xl rounded-2xl p-8 text-center border border-gray-100 dark:border-slate-700 overflow-hidden relative transition-colors">
                
                <!-- Icon & Title -->
                <div class="flex flex-col items-center gap-4 mb-10 mt-2">
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-2xl shadow-sm border border-blue-100 dark:border-blue-800">
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
                    <div class="flex flex-col items-center gap-1">
                        <p class="text-gray-500 dark:text-slate-400 text-sm">Hubungkan akun WhatsApp Anda untuk pengiriman notifikasi otomatis.</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 border border-blue-200 dark:border-blue-800 mt-2">
                            Server: <span class="ml-1 font-bold">{{ $serverName }}</span>
                        </span>
                    </div>
                </div>

                <!-- Input Session -->
                <div class="text-left mb-8">
                    <label class="block text-gray-700 dark:text-slate-300 text-xs font-bold mb-2 uppercase tracking-widest">Device ID / Session Name</label>
                    <div class="relative">
                        <input type="text" 
                               readonly 
                               value="{{ $deviceId }}"
                               class="w-full bg-gray-50 dark:bg-slate-900 dark:text-slate-300 border border-gray-200 dark:border-slate-700 text-gray-700 rounded-xl px-4 py-3.5 focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all font-mono text-center text-lg font-bold"
                               placeholder="Nomor belum diatur">
                    </div>
                    @if(!$deviceId)
                        <p class="mt-2 text-red-500 text-xs text-center">Silahkan lengkapi nomor HP di <a href="{{ route('profile') }}" class="underline font-bold">Profil</a> Anda.</p>
                    @endif
                </div>

                <!-- QR Area -->
                <div class="relative flex flex-col justify-center items-center bg-gray-50 dark:bg-slate-900/50 border-2 border-dashed border-gray-200 dark:border-slate-700 p-8 rounded-2xl min-h-[350px] transition-all duration-500">
                    
                    <!-- Status Message -->
                    <div class="mb-4 text-center">
                        <p class="text-gray-500 text-sm font-medium">{{ $statusMessage }}</p>
                    </div>

                    @if($deviceId)
                        <!-- Ready State -->
                        @if($isConnected)
                            <div class="text-green-600 font-bold flex flex-col items-center gap-4 py-10 transition-all">
                                <div class="bg-green-100 p-5 rounded-full shadow-inner border border-green-200">
                                    <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-black">WhatsApp Terhubung!</p>
                                    <p class="text-green-500 font-medium text-sm mt-1">Sistem siap mengirimkan pesan.</p>
                                </div>
                            </div>
                        @endif

                        <!-- QR Code Image -->
                        @if($qrCode && !$isConnected)
                            <div class="mb-4">
                                <p class="text-gray-700 dark:text-slate-200 font-bold">Scan QR Code di bawah</p>
                            </div>
                            <div class="bg-white p-4 rounded-xl shadow-lg border border-gray-100 transform transition-transform hover:scale-105 duration-300">
                                <img src="{{ $qrCode }}" alt="QR Code" class="max-w-full h-[250px] w-[250px] transition-all">
                            </div>
                        @endif

                        <!-- Connect Button -->
                        @if($showConnectButton)
                            <div class="mt-4">
                                <button wire:click="generateQr" 
                                        wire:loading.attr="disabled"
                                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-md">
                                    <span wire:loading.remove wire:target="generateQr">Koneksi WhatsApp</span>
                                    <span wire:loading wire:target="generateQr">Memproses...</span>
                                </button>
                            </div>
                        @endif
                    @else
                        <!-- Placeholder for no Device ID -->
                        <div class="text-gray-400 dark:text-slate-500 text-sm italic">
                            Menunggu ID Perangkat...
                        </div>
                    @endif
                </div>

                <div class="mt-10 flex flex-col gap-4">
                    <button wire:click="reconnect" 
                            wire:loading.attr="disabled"
                            class="w-full bg-gray-900 dark:bg-slate-700 hover:bg-black dark:hover:bg-slate-600 text-white font-bold py-4 rounded-xl transition-all shadow-lg hover:shadow-xl active:scale-95 flex items-center justify-center gap-2">
                        <span wire:loading wire:target="reconnect" class="animate-spin mr-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="4" stroke="currentColor" stroke-dasharray="32" stroke-dashoffset="32"></circle></svg>
                        </span>
                        <svg wire:loading.remove wire:target="reconnect" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        CEK STATUS KONEKSI / REFRESH
                    </button>
                    <p class="text-gray-400 dark:text-slate-500 text-xs italic">Pastikan WA anda tetap aktif dan terhubung internet.</p>
                </div>
            </div>
        </div>
    </div></div>
