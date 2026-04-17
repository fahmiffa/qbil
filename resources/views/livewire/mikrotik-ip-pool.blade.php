<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">IP Pool MikroTik</h2>
    </x-slot>

    <div class="w-full">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-100">
            <div class="p-4 sm:p-6">

                @if(session('message'))
                    <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('message') }}</span>
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Header Actions -->
                <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-800">IP Pool Management</h3>
                        <p class="text-sm text-slate-500">Kumpulan rentang alamat IP untuk DHCP, Hotspot, dan PPPoE.</p>
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="loadPools()" wire:loading.attr="disabled" wire:target="loadPools"
                            class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-semibold py-2 px-4 rounded-xl transition-all shadow-sm">
                            <svg class="w-4 h-4" wire:loading.class="animate-spin" wire:target="loadPools" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Refresh
                        </button>
                        <button wire:click="openCreate()" class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold py-2.5 px-5 rounded-xl transition-all shadow-lg shadow-teal-600/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah Pool
                        </button>
                    </div>
                </div>

                <!-- Error State -->
                @if($error)
                    <div class="bg-red-50 border border-red-100 text-red-700 px-6 py-8 rounded-2xl text-center">
                        <svg class="w-12 h-12 text-red-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <p class="font-bold text-lg leading-none">Terjadi Masalah</p>
                        <p class="text-sm mt-2 opacity-80">{{ $error }}</p>
                        <button wire:click="loadPools()" class="mt-4 text-blue-600 font-semibold underline">Coba Lagi</button>
                    </div>

                <!-- Loading State -->
                @elseif($loading)
                    <div class="flex flex-col justify-center items-center py-20 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
                        <div class="relative w-12 h-12">
                            <div class="absolute inset-0 border-4 border-teal-100 rounded-full"></div>
                            <div class="absolute inset-0 border-4 border-teal-500 rounded-full border-t-transparent animate-spin"></div>
                        </div>
                        <span class="mt-4 font-medium text-slate-500">Sinkronisasi data...</span>
                    </div>

                <!-- Table -->
                @else
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-widest">ID</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-widest">Nama Pool</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-widest">IP Range</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-widest">Next Pool</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-widest">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100">
                                @forelse($pools as $pool)
                                    <tr class="hover:bg-slate-50/80 transition-all group">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400 font-mono">{{ $pool['id'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-teal-50 flex items-center justify-center text-teal-600">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                                </div>
                                                <span class="text-sm font-bold text-slate-800">{{ $pool['name'] ?? '-' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-600">
                                            <code class="px-2 py-1 bg-slate-100 rounded text-slate-700 font-mono">{{ $pool['ranges'] ?? '-' }}</code>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                            @if(($pool['next-pool'] ?? '') !== '')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-100">
                                                    {{ $pool['next-pool'] }}
                                                </span>
                                            @else
                                                <span class="text-slate-300 italic">None</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right space-x-1">
                                            <button wire:click="openEdit('{{ $pool['id'] }}')" 
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-blue-600 hover:bg-blue-50 rounded-lg transition-all">
                                                Edit
                                            </button>
                                            <button wire:click="delete('{{ $pool['id'] }}')" 
                                                wire:confirm="Yakin ingin menghapus Pool '{{ $pool['name'] }}'?"
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-red-500 hover:bg-red-50 rounded-lg transition-all group-hover:opacity-100 opacity-0 lg:opacity-100">
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-16 text-center">
                                            <div class="text-slate-300 mb-2">
                                                <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4a2 2 0 012-2m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                            </div>
                                            <p class="text-slate-500 font-medium italic">Tidak ada IP Pool ditemukan di MikroTik.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Modal Form -->
                @if($showModal)
                    <div class="fixed z-50 inset-0 overflow-y-auto">
                        <div class="flex items-center justify-center min-h-screen px-4 py-6">
                            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="$wire.closeModal()"></div>
                            <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md transform transition-all overflow-hidden border border-slate-200">
                                <!-- Modal Header -->
                                <div class="px-8 pt-8 pb-4">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-2xl font-black text-slate-800 tracking-tight">
                                            {{ $isEditing ? 'Update IP Pool' : 'New IP Pool' }}
                                        </h3>
                                        <button wire:click="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    <p class="text-sm text-slate-500 mt-1">Konfigurasi rentang IP untuk layanan MikroTik.</p>
                                </div>

                                <form wire:submit.prevent="save">
                                    <div class="px-8 py-6 space-y-5">
                                        <!-- Pool Name -->
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 px-1">Nama Pool</label>
                                            <input type="text" wire:model="name" placeholder="E.g: pool-hotspot"
                                                class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm font-semibold focus:bg-white focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all outline-none">
                                            @error('name') <p class="text-red-500 text-xs mt-2 px-1">{{ $message }}</p> @enderror
                                        </div>

                                        <!-- IP Ranges -->
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 px-1">IP Ranges</label>
                                            <input type="text" wire:model="ranges" placeholder="E.g: 10.10.10.2-10.10.10.254"
                                                class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm font-semibold focus:bg-white focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all outline-none">
                                            @error('ranges') <p class="text-red-500 text-xs mt-2 px-1">{{ $message }}</p> @enderror
                                            <p class="text-[10px] text-slate-400 mt-2 italic px-1">Gunakan tanda hubung (-) untuk range atau koma (,) untuk list IP.</p>
                                        </div>

                                        <!-- Next Pool -->
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 px-1">Next Pool (Optional)</label>
                                            <select wire:model="next_pool" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm font-semibold focus:bg-white focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all outline-none appearance-none">
                                                <option value="none">-- None --</option>
                                                @foreach($pools as $p)
                                                    @php $pName = $p['name'] ?? ''; @endphp
                                                    @if($pName !== '' && $pName !== $name)
                                                        <option value="{{ $pName }}">{{ $pName }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Modal Footer -->
                                    <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100 flex gap-3">
                                        <button type="button" wire:click="closeModal()"
                                            class="flex-1 px-4 py-3 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-2xl hover:bg-slate-50 transition-all">
                                            Cancel
                                        </button>
                                        <button type="submit"
                                            class="flex-1 px-4 py-3 text-sm font-bold text-white bg-teal-600 rounded-2xl hover:bg-teal-700 shadow-lg shadow-teal-600/30 transition-all">
                                            {{ $isEditing ? 'Save Changes' : 'Create Pool' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
