<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M11 6a13 13 0 0 0 8.4-2.8A1 1 0 0 1 21 4v12a1 1 0 0 1-1.6.8A13 13 0 0 0 11 14H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z" />
                <path d="M6 14a12 12 0 0 0 2.4 7.2 2 2 0 0 0 3.2-2.4A8 8 0 0 1 10 14" />
                <path d="M8 6v8" />
            </svg>
            Broadcast WhatsApp
        </h2>
    </x-slot>

    <div class="w-full">
        <div class="p-0"
            x-data="{
                dropdownOpen: false,
                toggleAll: $wire.entangle('sendToAll'),
             }">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

                {{-- ===== LEFT COLUMN: FORM CARD ===== --}}
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-slate-800 shadow-xl rounded-2xl border border-gray-100 dark:border-slate-700 overflow-hidden">

                        {{-- Header gradient --}}
                        <div class="bg-gradient-to-r from-emerald-500 to-teal-600 p-6 flex items-center gap-4">
                            <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 6a13 13 0 0 0 8.4-2.8A1 1 0 0 1 21 4v12a1 1 0 0 1-1.6.8A13 13 0 0 0 11 14H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z" />
                                    <path d="M6 14a12 12 0 0 0 2.4 7.2 2 2 0 0 0 3.2-2.4A8 8 0 0 1 10 14" />
                                    <path d="M8 6v8" />
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-white font-extrabold text-xl tracking-tight">Broadcast Pesan WhatsApp</h1>
                                <p class="text-emerald-100 text-sm mt-0.5">Kirim pesan massal ke pelanggan</p>
                            </div>
                        </div>

                        <div class="p-6 space-y-6">

                            {{-- ===== DARI (sender) ===== --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1.5">Dari (Nomor WA Anda)</label>
                                <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                                    </svg>
                                    <span class="text-slate-700 dark:text-slate-200 font-mono text-sm font-semibold">
                                        {{ $from ?? 'Belum diatur — silahkan lengkapi nomor di Profil' }}
                                    </span>
                                </div>
                            </div>

                            {{-- ===== PENERIMA (select-search) ===== --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1.5">Penerima</label>

                                {{-- Toggle kirim semua --}}
                                <label class="flex items-center gap-3 mb-3 cursor-pointer select-none group w-fit">
                                    <div class="relative">
                                        <input type="checkbox" wire:model.live="sendToAll" class="sr-only peer" id="sendToAllCheck">
                                        <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 rounded-full peer-checked:bg-emerald-500 transition-colors duration-200 peer-focus:ring-2 peer-focus:ring-emerald-400/50"></div>
                                        <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200 peer-checked:translate-x-5"></div>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">Kirim ke semua pelanggan</span>
                                </label>

                                @if(!$sendToAll)
                                {{-- Chips of selected customers --}}
                                @if(count($selectedCustomerData) > 0)
                                <div class="flex flex-wrap gap-2 mb-2">
                                    @foreach($selectedCustomerData as $cust)
                                    <span class="inline-flex items-center gap-1.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700 rounded-full px-3 py-1 text-xs font-semibold">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        {{ $cust->name }}
                                        <span class="opacity-60">· {{ $cust->phone }}</span>
                                        <button type="button" wire:click="removeCustomer({{ $cust->id }})" class="ml-0.5 hover:text-red-500 transition-colors">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </span>
                                    @endforeach
                                </div>
                                @endif

                                {{-- Search input + dropdown --}}
                                <div class="relative" x-data="{ open: false }">
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-slate-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </span>
                                        <input
                                            type="text"
                                            wire:model.live.debounce.300ms="search"
                                            @focus="open = true"
                                            @click.outside="open = false"
                                            placeholder="Cari nama / nomor / ID pelanggan..."
                                            class="w-full pl-10 pr-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-400/50 focus:border-emerald-400 transition-all">
                                        @if($search)
                                        <button type="button" wire:click="$set('search', '')" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                        @endif
                                    </div>

                                    {{-- Dropdown results --}}
                                    @if(count($suggestions) > 0)
                                    <div
                                        x-show="open"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        class="absolute z-50 top-full mt-1 left-0 right-0 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl overflow-hidden">
                                        @foreach($suggestions as $cust)
                                        <button
                                            type="button"
                                            wire:click="addCustomer({{ $cust->id }})"
                                            @click="open = false"
                                            class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors group">
                                            <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center shrink-0">
                                                <span class="text-emerald-600 dark:text-emerald-400 font-bold text-xs">{{ strtoupper(substr($cust->name, 0, 2)) }}</span>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">{{ $cust->name }}</p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $cust->phone }} @if($cust->id_pelanggan)<span class="ml-2 opacity-60">· {{ $cust->id_pelanggan }}</span>@endif</p>
                                            </div>
                                            <svg class="w-4 h-4 text-emerald-500 ml-auto opacity-0 group-hover:opacity-100 shrink-0 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                        @endforeach
                                    </div>
                                    @elseif($search && count($suggestions) === 0)
                                    <div
                                        x-show="open"
                                        class="absolute z-50 top-full mt-1 left-0 right-0 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl px-4 py-3 text-sm text-slate-500 dark:text-slate-400 text-center">
                                        Pelanggan tidak ditemukan.
                                    </div>
                                    @endif
                                </div>

                                <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">
                                    Kosongkan pilihan untuk kirim ke semua pelanggan yang memiliki nomor WA.
                                </p>
                                @else
                                <div class="flex items-center gap-2 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 rounded-xl px-4 py-3">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Semua pelanggan yang memiliki nomor WA akan menerima pesan ini.</span>
                                </div>
                                @endif
                            </div>

                            {{-- ===== PESAN ===== --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1.5">Pesan Broadcast</label>
                                <textarea
                                    wire:model="message"
                                    rows="5"
                                    placeholder="Ketik pesan yang akan dikirim ke pelanggan..."
                                    class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-400/50 focus:border-emerald-400 transition-all resize-none leading-relaxed"></textarea>
                                @error('message')
                                <p class="mt-1.5 text-xs text-red-500 font-semibold">{{ $message }}</p>
                                @enderror
                                <div class="flex justify-end mt-1">
                                    <span class="text-xs text-slate-400 dark:text-slate-500">{{ strlen($message) }} karakter</span>
                                </div>
                            </div>

                            {{-- ===== PROGRESS BAR ===== --}}
                            @if($isSending || $isDone)
                            <div class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl p-4 space-y-3"
                                wire:poll.500ms="{{ $isSending ? 'checkProgress' : '' }}">
                                <div class="flex items-center justify-between text-sm font-semibold">
                                    <span class="text-slate-700 dark:text-slate-200">{{ $statusMessage }}</span>
                                    <span class="text-emerald-600 dark:text-emerald-400 font-bold tabular-nums">
                                        {{ $sentCount }} / {{ $totalCount }}
                                    </span>
                                </div>
                                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-3 overflow-hidden">
                                    <div
                                        class="h-3 rounded-full transition-all duration-500 {{ $isDone ? 'bg-emerald-500' : 'bg-blue-500 animate-pulse' }}"
                                        style="width: {{ $totalCount > 0 ? round(($sentCount / $totalCount) * 100) : 0 }}%"></div>
                                </div>
                                @if($isDone)
                                <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 text-sm font-semibold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Semua pesan berhasil diantrekan ke sistem
                                </div>
                                @endif
                            </div>
                            @endif

                            {{-- ===== BUTTONS ===== --}}
                            <div class="flex gap-3 pt-2">
                                @if($isDone)
                                <button
                                    type="button"
                                    wire:click="resetForm"
                                    class="flex-1 flex items-center justify-center gap-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold py-3.5 rounded-xl transition-all text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Kirim Baru
                                </button>
                                @else
                                <button
                                    type="button"
                                    wire:click="sendBroadcast"
                                    wire:loading.attr="disabled"
                                    wire:target="sendBroadcast"
                                    class="flex-1 flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/40 active:scale-95 text-sm disabled:opacity-60 disabled:cursor-not-allowed">
                                    <span wire:loading.remove wire:target="sendBroadcast">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 6a13 13 0 0 0 8.4-2.8A1 1 0 0 1 21 4v12a1 1 0 0 1-1.6.8A13 13 0 0 0 11 14H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z" />
                                        </svg>
                                    </span>
                                    <span wire:loading wire:target="sendBroadcast">
                                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                    </span>
                                    <span wire:loading.remove wire:target="sendBroadcast">Kirim Broadcast</span>
                                    <span wire:loading wire:target="sendBroadcast">Mengantrekan...</span>
                                </button>
                                @endif
                            </div>{{-- ===== BUTTONS END ===== --}}

                        </div>{{-- end p-6 --}}
                    </div>{{-- end bg-white card --}}
                </div>{{-- end lg:col-span-2 --}}

                {{-- ===== RIGHT COLUMN: INFO CARDS ===== --}}
                <div class="space-y-4">
                    {{-- Info Broadcast --}}
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-xl p-4 flex gap-3">
                        <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
                            <p class="font-bold">Informasi Broadcast</p>
                            <p>Jika kolom penerima dikosongkan, pesan akan dikirim ke <strong>semua pelanggan</strong> yang terdaftar dengan nomor WA Anda.</p>
                            <p>Pengiriman pesan masal dalam jumlah banyak punya resiko akun WhatsApp ada ke <strong>Banned</strong>, gunakan dengan bijak</p>
                        </div>
                    </div>

                    {{-- Syarat & Ketentuan --}}
                    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl p-4 flex gap-3">
                        <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="text-sm text-amber-700 dark:text-amber-300 space-y-1">
                            <p class="font-bold">Syarat &amp; Ketentuan Nenimalisir Resiko Banned</p>
                            <p>1. Pastikan nomor Anda sudah di save di hp pelanggan</p>
                            <p>2. Pastikan nomor Anda punya riwayat chat dengan hp pelanggan</p>
                        </div>
                    </div>
                </div>{{-- end right col --}}

            </div>{{-- end grid --}}
        </div>
    </div>
</div>{{-- end root --}}