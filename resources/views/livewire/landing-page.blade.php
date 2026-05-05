<div class="min-h-screen bg-black text-white font-sans selection:bg-blue-500/30 selection:text-blue-200">
    <!-- Background Decoration -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] bg-blue-600/10 rounded-full blur-[120px]"></div>
        <div class="absolute top-[10%] -right-[5%] w-[40%] h-[40%] bg-blue-500/5 rounded-full blur-[100px]"></div>
    </div>

    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-black/60 backdrop-blur-xl border-b border-white/5">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="size-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="size-6 text-white">
                        <path d="M20.34 17.52a10 10 0 1 0-2.82 2.82" />
                        <circle cx="19" cy="19" r="2" />
                        <path d="m13.41 13.41 4.18 4.18" />
                        <circle cx="12" cy="12" r="2" />
                    </svg>
                </div>
                <span class="text-2xl font-black tracking-tighter uppercase italic">QBILL</span>
            </div>

            <div class="hidden md:flex items-center gap-8 text-sm font-bold text-white/60">
                <a href="#fitur" class="hover:text-blue-500 transition-colors uppercase tracking-widest">Fitur</a>
                <a href="#harga" class="hover:text-blue-500 transition-colors uppercase tracking-widest">Harga</a>
            </div>

            <div class="flex items-center gap-4">
                <a href="{{ route('login') }}" class="bg-white text-black px-8 py-2.5 rounded-full font-black text-sm hover:bg-blue-500 hover:text-white transition-all transform active:scale-95 shadow-lg shadow-white/5 uppercase tracking-[0.2em]">
                    Login
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-44 pb-32 px-6 overflow-hidden">
        <div class="max-w-7xl mx-auto text-center">
            <div class="inline-flex items-center gap-2 bg-blue-500/10 border border-blue-500/20 px-4 py-1.5 rounded-full mb-8">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                </span>
                <span class="text-[10px] font-black uppercase tracking-widest text-blue-400">Solusi Billing Terbaik #1</span>
            </div>

            <h1 class="text-5xl md:text-8xl font-black tracking-tight mb-8 leading-[0.95]">
                Kelola <span class="bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600 bg-clip-text text-transparent italic">RT/RW Net</span><br />
                Lebih Mudah.
            </h1>

            <p class="text-lg md:text-xl text-white/50 max-w-2xl mx-auto leading-relaxed mb-12 font-medium">
                Sistem billing otomatis, manajemen pelanggan terpadu, dan<br class="hidden md:block" /> pelaporan real-time untuk bisnis ISP rumahan Anda.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="#harga" class="group bg-gradient-to-r from-blue-500 to-blue-600 p-[1px] rounded-2xl shadow-2xl shadow-blue-500/20 hover:scale-105 active:scale-95 transition-all">
                    <div class="bg-black/20 group-hover:bg-transparent transition-colors px-10 py-4 rounded-[15px] flex items-center gap-3">
                        <span class="font-black text-lg uppercase tracking-widest">Cek Simulasi Harga</span>
                        <svg class="size-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="fitur" class="py-32 px-6 bg-white/0">
        <div class="max-w-7xl mx-auto">
            <div class="mb-20">
                <h2 class="text-4xl md:text-5xl font-black tracking-tight mb-4">Fitur Unggulan</h2>
                <p class="text-white/40 max-w-xl font-medium">Semua yang Anda butuhkan untuk memanagemen jaringan RT/RW Net<br /> Anda dari satu dashboard.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Card 1: Mikrotik -->
                <div class="group bg-white/[0.03] border border-white/5 p-8 rounded-[40px] hover:bg-white/[0.06] hover:border-blue-500/30 transition-all duration-500">
                    <div class="size-14 bg-blue-500/10 rounded-2xl flex items-center justify-center mb-6 border border-blue-500/20 group-hover:bg-blue-500 group-hover:text-white transition-colors">
                        <svg class="size-6 text-blue-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-black mb-3">Manajemen Mikrotik</h3>
                    <p class="text-white/40 text-xs leading-relaxed font-medium">Integrasi router Mikrotik untuk isolir dan buka blokir otomatis secara real-time.</p>
                </div>

                <!-- Card 2: Payment QRIS -->
                <div class="group bg-white/[0.03] border border-white/5 p-8 rounded-[40px] hover:bg-white/[0.06] hover:border-blue-500/30 transition-all duration-500">
                    <div class="size-14 bg-blue-500/10 rounded-2xl flex items-center justify-center mb-6 border border-blue-500/20 group-hover:bg-blue-500 group-hover:text-white transition-colors">
                        <svg class="size-6 text-blue-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-black mb-3">Pembayaran Online</h3>
                    <p class="text-white/40 text-xs leading-relaxed font-medium">Support pembayaran online via QRIS, transfer bank dan offline dengan verifikasi instan.</p>
                </div>

                <!-- Card 3: WA Gateway -->
                <div class="group bg-white/[0.03] border border-white/5 p-8 rounded-[40px] hover:bg-white/[0.06] hover:border-blue-500/30 transition-all duration-500">
                    <div class="size-14 bg-blue-500/10 rounded-2xl flex items-center justify-center mb-6 border border-blue-500/20 group-hover:bg-blue-500 group-hover:text-white transition-colors">
                        <svg class="size-6 text-blue-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-black mb-3">WhatsApp Gateway</h3>
                    <p class="text-white/40 text-xs leading-relaxed font-medium">Notifikasi tagihan otomatis dan fitur broadcast pesan ke semua pelanggan.</p>
                </div>

                <!-- Card 4: Map Assets -->
                <div class="group bg-white/[0.03] border border-white/5 p-8 rounded-[40px] hover:bg-white/[0.06] hover:border-blue-500/30 transition-all duration-500">
                    <div class="size-14 bg-blue-500/10 rounded-2xl flex items-center justify-center mb-6 border border-blue-500/20 group-hover:bg-blue-500 group-hover:text-white transition-colors">
                        <svg class="size-6 text-blue-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-black mb-3">Map Asset OLT/ODP</h3>
                    <p class="text-white/40 text-xs leading-relaxed font-medium">Visualisasi infrastruktur jaringan dengan map asset OLT, ODP, dan ONU.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="harga" class="py-32 px-6 relative overflow-hidden" x-data="{ 
        count: @entangle('customerCount'),
        get price() {
            if (this.count < 30) return '30.000';
            if (this.count >= 300) return '500.000';
            return (this.count * 1000).toLocaleString('id-ID');
        }
    }">
        <div class="absolute inset-0 bg-blue-500/5 blur-[120px] rounded-full -bottom-1/2 left-1/2 -translate-x-1/2 w-3/4 h-1/2"></div>

        <div class="max-w-7xl mx-auto text-center mb-20">
            <h2 class="text-5xl md:text-7xl font-black tracking-tight mb-6">Simulasi Harga</h2>
            <p class="text-white/40 max-w-2xl mx-auto font-medium leading-relaxed">Bayar sesuai jumlah pelanggan Anda. Semakin banyak pelanggan, semakin<br class="hidden md:block" /> efisien biaya operasional Anda.</p>
        </div>

        <div class="max-w-5xl mx-auto">
            <div class="bg-white/[0.02] border border-white/5 rounded-[50px] p-8 md:p-16 backdrop-blur-3xl shadow-2xl">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                    <!-- Left: Control -->
                    <div class="space-y-12">
                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="size-6 bg-blue-500 rounded-lg flex items-center justify-center">
                                        <svg class="size-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </div>
                                    <span class="font-black uppercase tracking-widest text-sm">Jumlah Pelanggan</span>
                                </div>
                                <span class="text-4xl font-black text-blue-500 italic" x-text="count"></span>
                            </div>

                            <div class="relative py-8">
                                <input type="range" x-model="count" min="1" max="500" step="1"
                                    class="w-full h-2 bg-white/10 rounded-lg appearance-none cursor-pointer accent-blue-500 hover:accent-blue-400 transition-all">
                                <div class="flex justify-between mt-4 text-[10px] font-black uppercase tracking-widest text-white/20">
                                    <span>0</span>
                                    <span>100</span>
                                    <span>300</span>
                                    <span>500+</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center gap-3 text-sm font-bold text-white/60">
                                <svg class="size-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Minimal 30 pelanggan (Rp 30.000)</span>
                            </div>
                            <div class="flex items-center gap-3 text-sm font-bold text-white/60">
                                <svg class="size-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Di bawah 300: Rp 1.000 / pelanggan</span>
                            </div>
                            <div class="flex items-center gap-3 text-sm font-bold text-white/60">
                                <svg class="size-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Di atas 300 flat: Rp 500.000</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Pricing Card -->
                    <div class="relative group">
                        <div class="absolute -inset-1 bg-gradient-to-r from-blue-500 to-blue-600 rounded-[40px] blur opacity-20 group-hover:opacity-40 transition duration-1000"></div>
                        <div class="relative bg-black p-10 md:p-12 rounded-[40px] border border-white/5 space-y-8">
                            <div class="space-y-1">
                                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-white/40">Estimasi Biaya</span>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-5xl md:text-6xl font-black text-white italic">Rp <span x-text="price"></span></span>
                                </div>
                                <span class="text-xs font-bold text-white/40 uppercase tracking-widest">per bulan</span>
                            </div>

                            <a href="https://wa.me/6285640431181?text=Halo%20QBILL,%20saya%20ingin%20berlangganan%20untuk%20"
                                x-bind:href="'https://wa.me/6285640431181?text=Halo%20QBILL,%20saya%20ingin%20berlangganan%20untuk%20' + count + '%20pelanggan.'"
                                target="_blank"
                                class="block w-full bg-white text-black py-6 rounded-3xl text-center font-black text-xl hover:bg-blue-500 hover:text-white transition-all shadow-xl shadow-white/5 active:scale-95 uppercase tracking-[0.2em]">
                                Berlangganan
                            </a>

                            <p class="text-center text-[10px] font-black text-white/20 uppercase tracking-widest">Konsultasi via WhatsApp (Fast Response)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-20 px-6 border-t border-white/5">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="flex items-center gap-3 opacity-50">
                <div class="size-8 bg-blue-500/10 rounded-lg flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="size-4 text-white">
                        <path d="M20.34 17.52a10 10 0 1 0-2.82 2.82" />
                        <circle cx="19" cy="19" r="2" />
                        <path d="m13.41 13.41 4.18 4.18" />
                        <circle cx="12" cy="12" r="2" />
                    </svg>
                </div>
                <span class="text-lg font-black tracking-tighter uppercase italic">QBILL</span>
            </div>

            <p class="text-[10px] font-black text-white/20 uppercase tracking-[0.4em]">
                &copy; {{ date('Y') }} QBILL PT. QUEEN LAB CODE. All rights reserved.
            </p>
        </div>
    </footer>
</div>