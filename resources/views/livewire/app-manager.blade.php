<div>
    <div class="w-full py-6 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-3xl border border-slate-100 dark:border-slate-700 transition-colors">
            <div class="p-8">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                    <div>
                        <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Konfigurasi Sistem & Notifikasi</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-widest font-semibold mt-1">Kelola otomasi tagihan, isolir, dan template pesan WhatsApp</p>
                    </div>
                </div>
                <form wire:submit.prevent="save" class="space-y-8">
                    @if(auth()->user()->features()->whereIn('parameter', ['pppoe', 'static'])->exists())
                    <!-- SECTION: OTOMASI SISTEM -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 p-6 bg-slate-50/50 dark:bg-slate-900/30 rounded-[2rem] border border-slate-100 dark:border-slate-700 transition-colors">
                        <!-- Generate Invoice -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <label class="font-black text-xs text-emerald-600 dark:text-emerald-400 uppercase tracking-[0.2em]">Otomasi: Generate Invoice</label>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <span class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5 block">Rentang Hari</span>
                                    <div class="flex items-center gap-3">
                                        <input wire:model="invoice_gen_days" type="number" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-emerald-500 rounded-xl text-sm px-4 py-2.5 transition-all font-bold">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter shrink-0">Hari (H-n)</span>
                                    </div>
                                    @error('invoice_gen_days') <span class="text-[10px] text-red-500 mt-1 font-bold">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <span class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase mb-1.5 block">Waktu Eksekusi</span>
                                    <div class="flex items-center gap-3">
                                        <input wire:model="invoice_gen_time" type="time" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-emerald-500 rounded-xl text-sm px-4 py-2.5 transition-all font-bold">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter shrink-0">WIB</span>
                                    </div>
                                    @error('invoice_gen_time') <span class="text-[10px] text-red-500 mt-1 font-bold">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Isolir Otomatis -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-red-600 dark:text-red-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <label class="font-black text-xs text-red-600 dark:text-red-400 uppercase tracking-[0.2em]">Otomasi: Isolir Otomatis</label>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <span class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase mb-1.5 block">Rentang Hari</span>
                                    <div class="flex items-center gap-3">
                                        <input wire:model="isolate_days" type="number" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-red-500 rounded-xl text-sm px-4 py-2.5 transition-all font-bold">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter shrink-0">Hari (H+n)</span>
                                    </div>
                                    @error('isolate_days') <span class="text-[10px] text-red-500 mt-1 font-bold">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <span class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase mb-1.5 block">Waktu Eksekusi</span>
                                    <div class="flex items-center gap-3">
                                        <input wire:model="isolate_time" type="time" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-red-500 rounded-xl text-sm px-4 py-2.5 transition-all font-bold">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter shrink-0">WIB</span>
                                    </div>
                                    @error('isolate_time') <span class="text-[10px] text-red-500 mt-1 font-bold">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(auth()->user()->features()->whereIn('parameter', ['pppoe', 'static'])->exists())
                    <!-- SECTION: JADWAL NOTIFIKASI & TEMPLATE -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                        {{-- Notifikasi 1 --}}
                        <div class="space-y-4 p-6 bg-blue-50/30 dark:bg-blue-900/10 rounded-[2rem] border border-blue-100/50 dark:border-blue-800/30">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-600/20">
                                        <span class="text-xs font-black">1</span>
                                    </div>
                                    <label class="font-black text-xs text-slate-800 dark:text-white uppercase tracking-[0.2em]">Pengingat Tagihan Pertama</label>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Hari</span>
                                    <input wire:model="reminder_1_days" type="number" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 rounded-xl text-xs px-3 py-2 font-bold focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Waktu</span>
                                    <input wire:model="reminder_1_time" type="time" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 rounded-xl text-xs px-3 py-2 font-bold focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>

                            <div class="space-y-2">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Template Pesan</span>
                                <textarea wire:model="template" rows="8" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 rounded-2xl text-xs px-4 py-3 font-medium focus:ring-2 focus:ring-blue-500 transition-all" placeholder="Template notifikasi pertama..."></textarea>
                            </div>
                        </div>

                        {{-- Notifikasi 2 --}}
                        <div class="space-y-4 p-6 bg-orange-50/30 dark:bg-orange-900/10 rounded-[2rem] border border-orange-100/50 dark:border-orange-800/30">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-orange-500 flex items-center justify-center text-white shadow-lg shadow-orange-500/20">
                                        <span class="text-xs font-black">2</span>
                                    </div>
                                    <label class="font-black text-xs text-slate-800 dark:text-white uppercase tracking-[0.2em]">Pengingat Tagihan Kedua</label>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Hari</span>
                                    <input wire:model="reminder_2_days" type="number" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 rounded-xl text-xs px-3 py-2 font-bold focus:ring-2 focus:ring-orange-500">
                                </div>
                                <div>
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Waktu</span>
                                    <input wire:model="reminder_2_time" type="time" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 rounded-xl text-xs px-3 py-2 font-bold focus:ring-2 focus:ring-orange-500">
                                </div>
                            </div>

                            <div class="space-y-2">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Template Pesan</span>
                                <textarea wire:model="template_2" rows="8" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 rounded-2xl text-xs px-4 py-3 font-medium focus:ring-2 focus:ring-orange-500 transition-all" placeholder="Template notifikasi kedua..."></textarea>
                            </div>
                        </div>

                    </div>
                    @endif

                    {{-- VARIABEL INFO --}}
                    <div class="bg-slate-50/50 dark:bg-slate-900/30 border border-slate-100 dark:border-slate-700 rounded-3xl p-6 relative overflow-hidden transition-colors">
                        <div class="relative z-10">
                            <h4 class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.2em] mb-4">Daftar Variabel (Placeholders)</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-2">
                                <div class="text-[10px] font-mono"><span class="text-blue-600 dark:text-blue-400">{id_pelanggan}</span> <span class="text-slate-500 dark:text-slate-400">ID</span></div>
                                <div class="text-[10px] font-mono"><span class="text-blue-600 dark:text-blue-400">{name}</span> <span class="text-slate-500 dark:text-slate-400">Nama</span></div>
                                <div class="text-[10px] font-mono"><span class="text-blue-600 dark:text-blue-400">{package_name}</span> <span class="text-slate-500 dark:text-slate-400">Paket</span></div>
                                <div class="text-[10px] font-mono"><span class="text-blue-600 dark:text-blue-400">{total_amount}</span> <span class="text-slate-500 dark:text-slate-400">Total</span></div>
                                <div class="text-[10px] font-mono"><span class="text-blue-600 dark:text-blue-400">{due_date}</span> <span class="text-slate-500 dark:text-slate-400">Tempo</span></div>
                                <div class="text-[10px] font-mono"><span class="text-blue-600 dark:text-blue-400">{period}</span> <span class="text-slate-500 dark:text-slate-400">Bulan</span></div>
                                <div class="text-[10px] font-mono"><span class="text-blue-600 dark:text-blue-400">{public_url}</span> <span class="text-slate-500 dark:text-slate-400">Link</span></div>
                                <div class="text-[10px] font-mono"><span class="text-blue-600 dark:text-blue-400">{user_name}</span> <span class="text-slate-500 dark:text-slate-400">Admin</span></div>
                            </div>
                        </div>
                        <div class="absolute top-0 right-0 p-4 opacity-5 dark:opacity-10 text-slate-400 dark:text-slate-600">
                            <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM9 11H7V9h2v2zm4 0h-2V9h2v2zm4 0h-2V9h2v2z" />
                            </svg>
                        </div>
                    </div>

                    <!-- OTHER TEMPLATES -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        {{-- Pendaftaran --}}
                        <div class="space-y-3">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">WhatsApp: Pendaftaran Baru</label>
                            <textarea wire:model="registration_template" rows="6" class="w-full bg-slate-50 dark:bg-slate-900 border-slate-100 dark:border-slate-700 rounded-2xl text-xs px-4 py-3 font-medium focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        {{-- Pembayaran --}}
                        <div class="space-y-3">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">WhatsApp: Kwitansi Pembayaran</label>
                            <textarea wire:model="payment_template" rows="6" class="w-full bg-slate-50 dark:bg-slate-900 border-slate-100 dark:border-slate-700 rounded-2xl text-xs px-4 py-3 font-medium focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>

                    <!-- INSTRUCTIONS & QR -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        {{-- Instruksi --}}
                        <div class="space-y-3">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Instruksi Pembayaran (Invoice)</label>
                            <textarea wire:model="payment_instruction" rows="4" class="w-full bg-slate-50 dark:bg-slate-900 border-slate-100 dark:border-slate-700 rounded-2xl text-xs px-4 py-3 font-medium focus:ring-2 focus:ring-blue-500" placeholder="Rekening Bank, Dana, dll..."></textarea>
                        </div>
                        {{-- QR Payload --}}
                        <div class="space-y-3">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">QRIS Payload (Statis)</label>
                            <textarea wire:model="qr" rows="4" class="w-full bg-slate-50 dark:bg-slate-900 border-slate-100 dark:border-slate-700 rounded-2xl text-[10px] font-mono px-4 py-3 focus:ring-2 focus:ring-blue-500" placeholder="000201010211..."></textarea>
                        </div>
                    </div>

                    <!-- Action -->
                    <div class="pt-6 border-t border-slate-100 dark:border-slate-700 flex justify-end">
                        <button type="submit" wire:loading.attr="disabled" class="px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl transition-all shadow-xl shadow-blue-500/30 flex items-center gap-3">
                            <span wire:loading.remove>Simpan Seluruh Pengaturan</span>
                            <span wire:loading>Menyimpan...</span>
                        </button>
                    </div>
                </form>

                <!-- SECTION: METODE PEMBAYARAN -->
                <div class="mt-12 p-6 bg-indigo-50/30 dark:bg-indigo-900/10 rounded-[2rem] shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-600/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <label class="font-black text-xs text-indigo-600 dark:text-indigo-400 uppercase tracking-[0.2em]">Metode Pembayaran</label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="flex gap-2">
                                <input wire:model="new_method_name" type="text" class="flex-1 bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 rounded-xl text-sm px-4 py-2.5 font-bold focus:ring-2 focus:ring-indigo-500" placeholder="Tambah Nama Metode (ex: Bank BCA, Dana, dll)">
                                <button type="button" wire:click="addMethod" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-[10px] font-black uppercase tracking-widest rounded-xl transition-all shadow-lg shadow-indigo-500/20">
                                    Tambah
                                </button>
                            </div>
                            @error('new_method_name') <span class="text-[10px] text-red-500 mt-1 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-3">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Daftar Metode</span>
                            <div class="flex flex-wrap gap-2">
                                @forelse($methods as $method)
                                <div class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm group">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-200">{{ $method->nama }}</span>
                                    <button type="button" wire:click="deleteMethod({{ $method->id }})" class="text-slate-400 hover:text-red-500 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                @empty
                                <div class="text-[10px] font-bold text-slate-400 uppercase italic">Belum ada metode pembayaran.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>