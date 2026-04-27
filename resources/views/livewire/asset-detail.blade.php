<div>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                {{ __('Asset') }} {{ $asset->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Sidebar Info -->
                <div class="space-y-6">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="p-6">
                            <h3 class="text-sm font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-4">Informasi Aset</h3>
                            
                            <div class="space-y-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-slate-500">Nama Aset</span>
                                    <span class="text-lg font-bold text-slate-800 dark:text-white">{{ $asset->name }}</span>
                                </div>

                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-slate-500">Kategori</span>
                                    <div>
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-tight bg-blue-50 text-blue-600 border border-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800">
                                            {{ $asset->category ?? 'Lainnya' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-slate-500">ID Aset</span>
                                    <span class="font-mono text-blue-500">#{{ $asset->id }}</span>
                                </div>

                                <div class="pt-4 border-t border-slate-100 dark:border-slate-700">
                                    <span class="text-xs text-slate-500 block mb-1">Alamat Lokasi</span>
                                    <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed">
                                        {{ $asset->address ?? 'Alamat tidak diatur' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location Card -->
                    @if($asset->latitude && $asset->longitude)
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="p-6">
                            <h3 class="text-sm font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-4">Lokasi Geografis</h3>
                            
                            <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 h-48 mb-4 shadow-inner relative group">
                                <div class="absolute inset-0 bg-slate-100 dark:bg-slate-900 flex items-center justify-center">
                                    <div class="text-center">
                                        <svg class="w-10 h-10 text-red-500 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                        <p class="text-[10px] font-mono text-slate-500">{{ $asset->latitude }}</p>
                                        <p class="text-[10px] font-mono text-slate-500">{{ $asset->longitude }}</p>
                                    </div>
                                </div>
                            </div>

                            <a href="https://www.google.com/maps?q={{ $asset->latitude }},{{ $asset->longitude }}" target="_blank" 
                               class="flex items-center justify-center gap-2 w-full py-2.5 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-xl text-sm font-bold hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-all border border-blue-100 dark:border-blue-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Buka di Google Maps
                            </a>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Main Content (Customer List) -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 005.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                Pelanggan Terhubung
                            </h3>
                            <div class="flex items-center gap-3">
                                <button wire:click="openBroadcast" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                                    Broadcast
                                </button>
                                <span class="bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 px-3 py-1 rounded-full text-xs font-black">
                                    {{ $asset->customers->count() }} Pelanggan
                                </span>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-700">
                                <thead class="bg-slate-50/50 dark:bg-slate-900/50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">Pelanggan</th>
                                        <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">Username & Profil</th>
                                        <th class="px-6 py-4 text-center text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">Status</th>
                                        <th class="px-6 py-4 text-right text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 w-20">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    @forelse($asset->customers as $customer)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 font-bold uppercase">
                                                    {{ substr($customer->name, 0, 2) }}
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ $customer->name }}</span>
                                                    <span class="text-[10px] text-slate-500 font-mono">{{ $customer->id_pelanggan }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-mono text-blue-600 dark:text-blue-400">{{ $customer->username }}</span>
                                                <span class="text-[10px] text-slate-400">{{ $customer->ppp_profile }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-tighter {{ $customer->status === 'active' ? 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400' }}">
                                                {{ $customer->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('customers.detail', $customer->id) }}" class="p-2 text-slate-400 hover:text-blue-600 transition-colors block">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center gap-3 text-slate-400 dark:text-slate-500">
                                                <svg class="w-12 h-12 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 005.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                <p class="text-sm italic">Belum ada pelanggan yang terhubung ke aset ini.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Broadcast -->
    @if($isBroadcastOpen)
        <div class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" wire:click="closeBroadcast()"></div>
                
                <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg z-10 border border-slate-200 dark:border-slate-700 overflow-hidden transform transition-all"
                     x-on:click.stop>
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50">
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                            Broadcast WhatsApp
                        </h3>
                        <button wire:click="closeBroadcast()" class="text-slate-500 hover:text-red-500 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="sendBroadcast">
                        <div class="px-6 py-6 space-y-4">
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-100 dark:border-blue-800">
                                <p class="text-xs text-blue-700 dark:text-blue-300 leading-relaxed">
                                    Pesan akan dikirimkan ke <strong>{{ $asset->customers->count() }} pelanggan</strong> yang terhubung ke aset ini melalui antrean latar belakang dengan jeda 10 detik.
                                </p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Pesan WhatsApp</label>
                                <textarea wire:model="broadcastMessage" rows="8" placeholder="Tulis pesan Anda di sini..."
                                    class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-colors @error('broadcastMessage') border-red-400 @enderror"></textarea>
                                @error('broadcastMessage') <p class="text-red-500 text-[10px] mt-1 font-semibold">{{ $message }}</p> @enderror
                                
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span class="text-[9px] text-slate-400 uppercase font-bold">Variabel:</span>
                                    <button type="button" x-on:click="$wire.broadcastMessage += ' {name}'" class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700 rounded text-[9px] font-mono text-slate-600 dark:text-slate-300 hover:bg-slate-200">{name}</button>
                                    <button type="button" x-on:click="$wire.broadcastMessage += ' {id_pelanggan}'" class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700 rounded text-[9px] font-mono text-slate-600 dark:text-slate-300 hover:bg-slate-200">{id_pelanggan}</button>
                                    <button type="button" x-on:click="$wire.broadcastMessage += ' {address}'" class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700 rounded text-[9px] font-mono text-slate-600 dark:text-slate-300 hover:bg-slate-200">{address}</button>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="px-8 py-5 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3 rounded-b-2xl">
                            <button type="button" wire:click="closeBroadcast()"
                                class="px-5 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 transition-all uppercase tracking-widest">
                                Batal
                            </button>
                            <button type="submit" wire:loading.attr="disabled"
                                class="px-8 py-2.5 text-sm font-black text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-[0_8px_20px_-4px_rgba(79,70,229,0.4)] hover:shadow-[0_12px_24px_-6px_rgba(79,70,229,0.5)] transform hover:-translate-y-0.5 transition-all duration-300 min-w-[120px] disabled:opacity-50">
                                <span wire:loading.remove>Kirim Sekarang</span>
                                <span wire:loading>Memproses...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
