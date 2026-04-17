<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('WhatsApp') }}
        </h2>
    </x-slot>

    <!-- Load Socket.io at the start -->
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>

    <div class="py-12" x-data="{
        qr: null,
        status: 'Menghubungkan ke server...',
        deviceId: @entangle('deviceId'),
        socket: null,
        isReady: false,
        
        init() {
            let checkIo = setInterval(() => {
                if (typeof io !== 'undefined') {
                    clearInterval(checkIo);
                    if (this.deviceId) {
                        this.startSocket();
                    } else {
                        this.status = 'Device ID tidak ditemukan.';
                    }
                }
            }, 100);
        },

        startSocket() {
            if (this.socket) this.socket.close();

            try {
                this.socket = io('https://broadcast.qlabcode.com', {
                    query: { id: this.deviceId },
                    transports: ['websocket', 'polling'] 
                });

                this.socket.on('connect', () => {
                    console.log('Connected to socket server. ID:', this.deviceId);
                    this.status = 'Menunggu QR Code...';
                    this.socket.emit('create-session', { id: this.deviceId });
                });

                this.socket.on('qr', (data) => {
                    console.log('Got QR Data:', data);
                    let url = '';
                    if (typeof data === 'string') {
                        url = data;
                    } else if (typeof data === 'object' && data !== null) {
                        url = data.qr || data.url || data.data || data.image || data.src || data.base64;
                        if (!url) {
                            // Find any string that is a data image
                            url = Object.values(data).find(v => typeof v === 'string' && v.startsWith('data:image'));
                        }
                        if (!url) {
                            // Fallback, if there's only one string
                            const strings = Object.values(data).filter(v => typeof v === 'string');
                            if (strings.length > 0) url = strings[0];
                        }
                    }
                    if (url) {
                        this.qr = url;
                        this.isReady = false;
                        this.status = 'Siap di Scan';
                    } else {
                        console.error('Bentuk data QR tidak dikenali:', data);
                        this.status = 'Menunggu format QR yang valid...';
                    }
                });

                this.socket.on('ready', () => {
                    console.log('Session ready!');
                    this.qr = null;
                    this.isReady = true;
                    this.status = 'Koneksi Berhasil!';
                });

                this.socket.on('disconnect', () => {
                    this.status = 'Terputus dari server.';
                    this.qr = null;
                    this.isReady = false;
                });
                
                this.socket.on('message', (msg) => {
                    console.log('Socket message:', msg);
                });

                this.socket.on('connect_error', (err) => {
                    console.error('Socket connect_error:', err.message);
                    this.status = 'Gagal terhubung: Server down/CORS.';
                });

            } catch (e) {
                console.error('Socket exception:', e);
                this.status = 'Gagal menghubungkan ke server.';
            }
        },

        reconnect() {
            this.qr = null;
            this.isReady = false;
            this.status = 'Melakukan inisialisasi ulang...';
            this.startSocket();
        }
    }">
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
                    <p class="text-gray-500 dark:text-slate-400 text-sm">Hubungkan akun WhatsApp Anda untuk pengiriman notifikasi otomatis.</p>
                </div>

                <!-- Input Session -->
                <div class="text-left mb-8">
                    <label class="block text-gray-700 dark:text-slate-300 text-xs font-bold mb-2 uppercase tracking-widest">Device ID / Session Name</label>
                    <div class="relative">
                        <input type="text" 
                               readonly 
                               x-model="deviceId"
                               class="w-full bg-gray-50 dark:bg-slate-900 dark:text-slate-300 border border-gray-200 dark:border-slate-700 text-gray-700 rounded-xl px-4 py-3.5 focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all font-mono text-center text-lg font-bold"
                               placeholder="Nomor belum diatur">
                    </div>
                    <template x-if="!deviceId">
                        <p class="mt-2 text-red-500 text-xs text-center">Silahkan lengkapi nomor HP di <a href="{{ route('profile') }}" class="underline font-bold">Profil</a> Anda.</p>
                    </template>
                </div>

                <!-- QR Area -->
                <div class="relative flex flex-col justify-center items-center bg-gray-50 dark:bg-slate-900/50 border-2 border-dashed border-gray-200 dark:border-slate-700 p-8 rounded-2xl min-h-[350px] transition-all duration-500">
                    
                    <!-- Loading Spinner -->
                    <div x-show="!qr && deviceId && !isReady" class="flex flex-col items-center gap-4">
                        <div class="animate-spin rounded-full h-12 w-12 border-4 border-gray-200 border-t-blue-600"></div>
                        <p class="text-gray-500 text-sm font-medium animate-pulse" x-text="status"></p>
                    </div>

                    <!-- Placeholder for no Device ID -->
                    <div x-show="!deviceId" class="text-gray-400 dark:text-slate-500 text-sm italic">
                        Menunggu ID Perangkat...
                    </div>

                    <!-- Ready State -->
                    <div x-show="isReady" 
                         x-transition:enter="transition ease-out duration-500"
                         x-transition:enter-start="scale-95 opacity-0"
                         x-transition:enter-end="scale-100 opacity-100"
                         class="text-green-600 font-bold flex flex-col items-center gap-4 py-10">
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

                    <!-- Scan Message Above QR -->
                    <div x-show="qr && !isReady" class="mb-4">
                        <p class="text-gray-700 dark:text-slate-200 font-bold">Scan QR Code di bawah</p>
                    </div>

                    <!-- QR Code Image -->
                    <template x-if="qr && !isReady">
                        <div class="bg-white p-4 rounded-xl shadow-lg border border-gray-100 transform transition-transform hover:scale-105 duration-300">
                            <img :src="qr" alt="QR Code" class="max-w-full h-[250px] w-[250px] transition-all">
                        </div>
                    </template>
                </div>

                <div class="mt-10 flex flex-col gap-4">
                    <button @click="reconnect" 
                            class="w-full bg-gray-900 dark:bg-slate-700 hover:bg-black dark:hover:bg-slate-600 text-white font-bold py-4 rounded-xl transition-all shadow-lg hover:shadow-xl active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        MUAT ULANG KONEKSI
                    </button>
                    <p class="text-gray-400 dark:text-slate-500 text-xs italic">Pastikan WA anda tetap aktif dan terhubung internet.</p>
                </div>
            </div>
        </div>
    </div></div>
