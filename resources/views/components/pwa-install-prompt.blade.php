<div x-data="{
        canInstall: false,
        isInstalled: window.PWA?.isInstalled || false,
        showBanner: false,
        showIosGuide: false,
        dismissed: localStorage.getItem('pwa_prompt_dismissed') === 'true',
        
        init() {
            // Check standalone mode
            if (this.isInstalled) return;

            // Listen for browser installable event
            window.addEventListener('pwa-installable', () => {
                this.canInstall = true;
                if (!this.dismissed) {
                    // Small delay for smooth entry
                    setTimeout(() => { this.showBanner = true; }, 1500);
                }
            });

            // Listen for successful installation
            window.addEventListener('pwa-installed', () => {
                this.canInstall = false;
                this.isInstalled = true;
                this.showBanner = false;
                this.showIosGuide = false;
            });

            // Listen for iOS instructions trigger
            window.addEventListener('pwa-show-ios-guide', () => {
                this.showIosGuide = true;
            });

            // If on iOS Safari and not installed & not dismissed
            const isIos = /iphone|ipad|ipod/.test(navigator.userAgent.toLowerCase());
            const isSafari = navigator.userAgent.toLowerCase().includes('safari') && !navigator.userAgent.toLowerCase().includes('chrome');
            if (isIos && isSafari && !this.isInstalled && !this.dismissed) {
                setTimeout(() => { this.showBanner = true; }, 3000);
            }
        },

        async install() {
            const result = await window.PWA.promptInstall();
            if (result === 'accepted') {
                this.showBanner = false;
            } else if (result === 'ios-guide') {
                this.showBanner = false;
                this.showIosGuide = true;
            }
        },

        dismiss() {
            this.showBanner = false;
            this.dismissed = true;
            localStorage.setItem('pwa_prompt_dismissed', 'true');
        }
    }"
    class="relative z-[9999]"
    style="display: none;"
    x-show="showBanner || showIosGuide"
    x-cloak>

    <!-- Floating Install Banner (Mobile & Desktop) -->
    <div x-show="showBanner"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-8 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-8 scale-95"
        class="fixed bottom-4 left-4 right-4 sm:left-auto sm:right-6 sm:bottom-6 sm:max-w-md w-auto bg-slate-900/95 text-white p-4 sm:p-5 rounded-2xl shadow-2xl border border-blue-500/30 backdrop-blur-xl">
        
        <div class="flex items-start gap-3.5">
            <!-- App Icon -->
            <div class="shrink-0 size-12 rounded-xl bg-gradient-to-br from-blue-600 to-sky-500 p-0.5 shadow-lg shadow-blue-500/25 flex items-center justify-center">
                <img src="{{ asset('icons/icon-96x96.png') }}" alt="QBIL" class="w-full h-full rounded-[10px] object-cover">
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <h3 class="font-extrabold text-sm sm:text-base text-white tracking-tight">Pasang Aplikasi QBIL</h3>
                    <button @click="dismiss()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <p class="text-xs text-slate-300 mt-0.5 leading-relaxed">
                    Install di desktop atau ponsel Anda untuk performa lebih cepat, hemat data, dan akses satu ketukan.
                </p>

                <!-- Actions -->
                <div class="mt-3.5 flex items-center gap-2">
                    <button @click="install()"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-blue-600 hover:bg-blue-500 active:scale-95 text-white text-xs sm:text-sm font-bold rounded-xl shadow-lg shadow-blue-600/30 transition-all">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Pasang Sekarang</span>
                    </button>
                    <button @click="dismiss()"
                        class="px-3 py-2 text-xs font-semibold text-slate-400 hover:text-white hover:bg-white/5 rounded-xl transition-colors">
                        Nanti
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- iOS Safari "Add to Home Screen" Instruction Modal -->
    <div x-show="showIosGuide"
        class="fixed inset-0 bg-slate-950/80 backdrop-blur-md flex items-end sm:items-center justify-center p-4 z-[9999]"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div @click.outside="showIosGuide = false"
            class="bg-slate-900 border border-slate-800 text-white rounded-3xl p-6 max-w-sm w-full shadow-2xl relative">
            
            <button @click="showIosGuide = false" class="absolute top-4 right-4 text-slate-400 hover:text-white p-1 rounded-lg">
                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <div class="text-center mb-5">
                <div class="inline-flex items-center justify-center size-14 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/30 mb-3">
                    <img src="{{ asset('icons/icon-96x96.png') }}" alt="QBIL" class="size-11 rounded-xl">
                </div>
                <h4 class="font-extrabold text-lg text-white">Pasang di iPhone / iPad</h4>
                <p class="text-xs text-slate-400 mt-1">Ikuti langkah mudah ini untuk menambahkan QBIL ke Layar Utama:</p>
            </div>

            <div class="space-y-3.5 text-xs text-slate-300">
                <div class="flex items-start gap-3 bg-slate-800/60 p-3 rounded-2xl border border-white/5">
                    <div class="size-6 rounded-full bg-blue-600/30 text-blue-400 font-bold flex items-center justify-center shrink-0">1</div>
                    <p class="leading-relaxed">
                        Ketuk ikon <strong>Bagikan (Share)</strong> <svg class="inline size-4 text-blue-400 align-text-bottom" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg> di bilah bawah browser Safari Anda.
                    </p>
                </div>

                <div class="flex items-start gap-3 bg-slate-800/60 p-3 rounded-2xl border border-white/5">
                    <div class="size-6 rounded-full bg-blue-600/30 text-blue-400 font-bold flex items-center justify-center shrink-0">2</div>
                    <p class="leading-relaxed">
                        Gulir menu ke bawah dan pilih <strong>"Tambah ke Layar Utama" (Add to Home Screen)</strong> <svg class="inline size-4 text-blue-400 align-text-bottom" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>.
                    </p>
                </div>

                <div class="flex items-start gap-3 bg-slate-800/60 p-3 rounded-2xl border border-white/5">
                    <div class="size-6 rounded-full bg-blue-600/30 text-blue-400 font-bold flex items-center justify-center shrink-0">3</div>
                    <p class="leading-relaxed">
                        Ketuk tombol <strong>"Tambah" (Add)</strong> di pojok kanan atas untuk selesai.
                    </p>
                </div>
            </div>

            <button @click="showIosGuide = false"
                class="w-full mt-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-xl transition-colors">
                Saya Mengerti
            </button>
        </div>
    </div>
</div>
