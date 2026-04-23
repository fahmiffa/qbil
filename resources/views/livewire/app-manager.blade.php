<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Pengaturan Aplikasi</h2>
    </x-slot>

    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-2xl border border-slate-100 dark:border-slate-700 transition-colors">
            <div class="p-6 border-b border-gray-200 dark:border-slate-700">
                
                <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-6">Konfigurasi Sistem & Notifikasi</h3>

                <form wire:submit.prevent="save" class="space-y-6">
                    <!-- Jadwal Otomatis Sistem -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 py-6 border-y border-slate-100 dark:border-slate-700 transition-colors">
                        <!-- Generate Invoice -->
                        <div class="space-y-4">
                            <label class="block font-bold text-sm text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">
                                Sistem: Generate Invoice
                            </label>
                            
                            <div class="space-y-4">
                                <div>
                                    <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase mb-1.5 block">Rentang Hari</span>
                                    <div class="flex items-center gap-3">
                                        <input wire:model="invoice_gen_days" type="number" class="w-24 border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl text-sm px-4 py-2.5 shadow-sm transition-colors">
                                        <span class="text-xs text-slate-500 dark:text-slate-400">Hari (H-n)</span>
                                    </div>
                                    @error('invoice_gen_days') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase mb-1.5 block">Waktu Eksekusi</span>
                                    <div class="flex items-center gap-3">
                                        <input wire:model="invoice_gen_time" type="time" class="w-32 border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl text-sm px-4 py-2.5 shadow-sm transition-colors">
                                        <span class="text-xs text-slate-500 dark:text-slate-400">WIB</span>
                                    </div>
                                    @error('invoice_gen_time') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Isolir Otomatis -->
                        <div class="space-y-4">
                            <label class="block font-bold text-sm text-red-600 dark:text-red-400 uppercase tracking-wider">
                                Sistem: Isolir Otomatis
                            </label>
                            
                            <div class="space-y-4">
                                <div>
                                    <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase mb-1.5 block">Rentang Hari</span>
                                    <div class="flex items-center gap-3">
                                        <input wire:model="isolate_days" type="number" class="w-24 border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl text-sm px-4 py-2.5 shadow-sm transition-colors">
                                        <span class="text-xs text-slate-500 dark:text-slate-400">Hari (H+n)</span>
                                    </div>
                                    @error('isolate_days') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase mb-1.5 block">Waktu Eksekusi</span>
                                    <div class="flex items-center gap-3">
                                        <input wire:model="isolate_time" type="time" class="w-32 border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl text-sm px-4 py-2.5 shadow-sm transition-colors">
                                        <span class="text-xs text-slate-500 dark:text-slate-400">WIB</span>
                                    </div>
                                    @error('isolate_time') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Jadwal Otomatis Pesan -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 py-6 border-b border-slate-100 dark:border-slate-700 transition-colors">
                        <!-- Notifikasi Pertama -->
                        <div class="space-y-4">
                            <label class="block font-bold text-sm text-slate-800 dark:text-white uppercase tracking-wider">
                                Pesan: Notifikasi Pertama
                            </label>
                            
                            <div class="space-y-4">
                                <div>
                                    <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase mb-1.5 block">Rentang Hari</span>
                                    <div class="flex items-center gap-3">
                                        <input wire:model="reminder_1_days" type="number" class="w-24 border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl text-sm px-4 py-2.5 shadow-sm transition-colors">
                                        <span class="text-xs text-slate-500 dark:text-slate-400">Hari (Gunakan - untuk H-n)</span>
                                    </div>
                                    @error('reminder_1_days') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase mb-1.5 block">Waktu Pengiriman</span>
                                    <div class="flex items-center gap-3">
                                        <input wire:model="reminder_1_time" type="time" class="w-32 border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl text-sm px-4 py-2.5 shadow-sm transition-colors">
                                        <span class="text-xs text-slate-500 dark:text-slate-400">WIB</span>
                                    </div>
                                    @error('reminder_1_time') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Notifikasi Kedua -->
                        <div class="space-y-4">
                            <label class="block font-bold text-sm text-slate-800 dark:text-white uppercase tracking-wider">
                                Pesan: Notifikasi Kedua
                            </label>
                            
                            <div class="space-y-4">
                                <div>
                                    <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase mb-1.5 block">Rentang Hari</span>
                                    <div class="flex items-center gap-3">
                                        <input wire:model="reminder_2_days" type="number" class="w-24 border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl text-sm px-4 py-2.5 shadow-sm transition-colors">
                                        <span class="text-xs text-slate-500 dark:text-slate-400">Hari (Contoh: 0 untuk hari H)</span>
                                    </div>
                                    @error('reminder_2_days') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase mb-1.5 block">Waktu Pengiriman</span>
                                    <div class="flex items-center gap-3">
                                        <input wire:model="reminder_2_time" type="time" class="w-32 border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl text-sm px-4 py-2.5 shadow-sm transition-colors">
                                        <span class="text-xs text-slate-500 dark:text-slate-400">WIB</span>
                                    </div>
                                    @error('reminder_2_time') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Template WhatsApp -->
                    <div>
                        <label class="block font-medium text-sm text-slate-700 dark:text-slate-300 mb-2" for="template">
                            Template Pesan WhatsApp Invoice
                        </label>
                        <textarea wire:model="template" id="template" rows="5" class="border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm block w-full px-4 py-3 sm:text-sm transition-colors" placeholder="Halo {name}, tagihan Anda..."></textarea>
                        
                        <div class="mt-3 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700 transition-colors">
                            <p class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2">Variabel Pilihan (Tagihan):</p>
                            <ul class="grid grid-cols-2 gap-2 text-[11px] text-emerald-700 dark:text-emerald-400 font-mono">
                                <li>{id_pelanggan} = ID Pelanggan</li>
                                <li>{name} = Nama Pelanggan</li>
                                <li>{phone} = Nomor WhatsApp</li>
                                <li>{address} = Alamat Lengkap</li>
                                <li>{package_name} = Nama Paket Internet</li>
                                <li>{invoice_number} = Nomor Invoice</li>
                                <li>{amount} = Nominal Paket</li>
                                <li>{unique_code} = Kode Unik Transfer</li>
                                <li>{total_amount} = Total Tagihan (Rp)</li>
                                <li>{due_date} = Tanggal Jatuh Tempo</li>
                                <li>{public_url} = Link Invoice Publik</li>
                            </ul>
                        </div>
                        @error('template') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Template Pendaftaran -->
                    <div>
                        <label class="block font-medium text-sm text-slate-700 dark:text-slate-300 mb-2" for="registration_template">
                            Template Pesan WhatsApp Pendaftaran
                        </label>
                        <textarea wire:model="registration_template" id="registration_template" rows="5" class="border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm block w-full px-4 py-3 sm:text-sm transition-colors" placeholder="Selamat datang {name}..."></textarea>
                        
                        <div class="mt-3 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700 transition-colors">
                            <p class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2">Variabel Pilihan (Pendaftaran):</p>
                            <ul class="grid grid-cols-2 gap-2 text-[11px] text-blue-700 dark:text-blue-400 font-mono">
                                <li>{name} = Nama Pelanggan</li>
                                <li>{username} = Username Akun</li>
                                <li>{password} = Password Akun</li>
                                <li>{package_name} = Nama Paket</li>
                                <li>{address} = Alamat Pelanggan</li>
                            </ul>
                        </div>
                        @error('registration_template') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Template Pembayaran -->
                    <div>
                        <label class="block font-medium text-sm text-slate-700 dark:text-slate-300 mb-2" for="payment_template">
                            Template Pesan WhatsApp Pembayaran (Lunas)
                        </label>
                        <textarea wire:model="payment_template" id="payment_template" rows="5" class="border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm block w-full px-4 py-3 sm:text-sm transition-colors" placeholder="Terima kasih atas pembayaran Anda..."></textarea>
                        
                        <div class="mt-3 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700 transition-colors">
                            <p class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2">Variabel Pilihan (Pembayaran):</p>
                            <ul class="grid grid-cols-2 gap-2 text-[11px] text-purple-700 dark:text-purple-400 font-mono">
                                <li>{name} = Nama Pelanggan</li>
                                <li>{address} = Alamat Lengkap</li>
                                <li>{amount} = Total Dibayar (Rp)</li>
                                <li>{package_name} = Nama Paket</li>
                                <li>{period} = Bulan/Periode Tagihan</li>
                                <li>{public_url} = Link Kwitansi/Invoice</li>
                            </ul>
                        </div>
                        @error('payment_template') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Instruksi Pembayaran -->
                    <div>
                        <label class="block font-medium text-sm text-slate-700 dark:text-slate-300 mb-2" for="payment_instruction">
                            Instruksi Pembayaran (Tampil di Invoice Publik)
                        </label>
                        <textarea wire:model="payment_instruction" id="payment_instruction" rows="4" class="border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm block w-full px-4 py-3 sm:text-sm transition-colors" placeholder="Silakan transfer ke nomor rekening:
Bank BCA: 12345678 a/n Admin
Dana: 0812345678"></textarea>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-2 italic">
                            * Pesan ini akan muncul di bagian bawah halaman invoice yang dibagikan ke pelanggan.
                        </p>
                        @error('payment_instruction') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Data QR Statis -->
                    <div class="p-5 bg-gradient-to-br from-slate-50 to-white dark:from-slate-900/50 dark:to-slate-800/20 rounded-2xl border border-slate-100 dark:border-slate-700">
                        <label class="block font-bold text-sm text-slate-700 dark:text-slate-300 mb-3" for="qr">
                            Data QR Static (Payload)
                        </label>
                        <div class="space-y-4">
                            <textarea wire:model="qr" id="qr" rows="4" class="border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm block w-full px-4 py-3 sm:text-sm font-mono transition-colors" placeholder="Masukkan 000201010211..."></textarea>
                            
                            <p class="text-xs text-slate-400 dark:text-slate-500 leading-relaxed">
                                <span class="text-blue-500 font-bold mr-1">Info:</span> Masukkan raw data/payload dari QRIS statis Anda di sini. Data ini akan digunakan untuk menghasilkan QR Code otomatis pada invoice pelanggan.
                            </p>
                            @error('qr') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Action -->
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-700 flex justify-end">
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 transition ease-in-out duration-150 shadow-lg shadow-blue-500/30">
                            Simpan Pengaturan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
