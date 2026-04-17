<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Interface MikroTik</h2>
    </x-slot>

    <div class="w-full">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-4 sm:p-6">

                @if(session('message'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('message') }}</div>
                @endif
                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
                @endif

                <!-- Header Actions -->
                <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Daftar Interface</h3>
                        <p class="text-sm text-gray-500">Manajemen interface (VLAN, Bridge, Physical) langsung dari router.</p>
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="loadInterfaces()" wire:loading.attr="disabled" wire:target="loadInterfaces"
                            class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold py-2 px-4 rounded-lg transition-all disabled:opacity-60 border border-slate-200">
                            <svg class="w-4 h-4" wire:loading.class="animate-spin" wire:target="loadInterfaces" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span wire:loading.remove wire:target="loadInterfaces">Refresh</span>
                            <span wire:loading wire:target="loadInterfaces">Loading...</span>
                        </button>
                        <button wire:click="openCreate()" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 px-4 rounded-lg transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah Interface
                        </button>
                    </div>
                </div>

                <!-- Error State -->
                @if($error)
                    <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-xl">
                        <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <p class="font-semibold">Koneksi Gagal</p>
                            <p class="text-sm mt-1">{{ $error }}</p>
                        </div>
                    </div>

                <!-- Loading State -->
                @elseif($loading)
                    <div class="flex justify-center items-center py-16">
                        <svg class="animate-spin w-10 h-10 text-blue-500" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="ml-3 text-gray-500">Mengambil data dari MikroTik...</span>
                    </div>

                <!-- Table -->
                @else
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <table class="min-w-full min-w-[900px] divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Komentar</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Running</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($interfaces as $idx => $iface)
                                    <tr class="hover:bg-gray-50 transition-colors {{ $iface['disabled'] ? 'opacity-50' : '' }}">
                                        <td class="px-4 py-3 text-sm text-gray-400">{{ $idx + 1 }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-gray-800">
                                            {{ $iface['name'] ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="px-2 py-0.5 text-xs font-medium rounded bg-slate-100 text-slate-600 uppercase">
                                                {{ $iface['type'] ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500 max-w-xs truncate">
                                            {{ $iface['comment'] ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                            @if($iface['running'])
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 leading-none">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5 animate-pulse"></span> OK
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">
                                                    Down
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                            @if($iface['disabled'])
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">Disabled</span>
                                            @else
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Enabled</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center space-x-1">
                                            <button wire:click="openEdit('{{ $iface['id'] }}')" 
                                                class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-all" title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            
                                            @if($iface['disabled'])
                                                <button wire:click="toggleInterface('{{ $iface['id'] }}', true)" class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition-all" title="Enable">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                </button>
                                            @else
                                                <button wire:click="toggleInterface('{{ $iface['id'] }}', false)" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Disable">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                </button>
                                            @endif

                                            @if(!in_array($iface['type'] ?? '', ['ether', 'wlan', 'bridge-port', 'loopback']))
                                                <button wire:click="delete('{{ $iface['id'] }}', '{{ $iface['type'] }}')" 
                                                    wire:confirm="Yakin ingin menghapus interface {{ $iface['name'] }}?"
                                                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Hapus">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-10 text-center text-gray-400 italic">Tidak ada interface ditemukan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Modal Form (Create/Edit) -->
                @if($showModal)
                    <div class="fixed z-50 inset-0 overflow-y-auto">
                        <div class="flex items-center justify-center min-h-screen px-4">
                            <div class="fixed inset-0 bg-black opacity-40"></div>
                            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10">
                                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                                    <h3 class="text-lg font-bold text-gray-800">
                                        {{ $isEditing ? 'Edit Interface' : 'Tambah Interface' }}
                                    </h3>
                                    <button wire:click="closeModal()" class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <form wire:submit.prevent="save">
                                    <div class="px-6 py-5 space-y-4">
                                        
                                        @if(!$isEditing)
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1">Tipe Interface</label>
                                            <select wire:model.live="formType" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                                                <option value="vlan">VLAN</option>
                                                <option value="bridge">Bridge</option>
                                            </select>
                                        </div>
                                        @endif

                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Interface</label>
                                            <input type="text" wire:model="formName" placeholder="Contoh: vlan-kantor" {{ $isEditing ? 'readonly' : '' }}
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 {{ $isEditing ? 'bg-gray-50' : '' }}">
                                            @error('formName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>

                                        @if($formType === 'vlan' && !$isEditing)
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700 mb-1">VLAN ID</label>
                                                    <input type="number" wire:model="formVlanId" placeholder="100"
                                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                                                    @error('formVlanId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Parent Iface</label>
                                                    <select wire:model="formParent" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                                                        <option value="">-- Pilih --</option>
                                                        @foreach($interfaces as $iface)
                                                            @if($iface['type'] === 'ether' || $iface['type'] === 'bridge')
                                                                <option value="{{ $iface['name'] }}">{{ $iface['name'] }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    @error('formParent') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                                </div>
                                            </div>
                                        @endif

                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1">Komentar</label>
                                            <input type="text" wire:model="formComment" placeholder="Deskripsi..."
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                                            @error('formComment') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                    <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
                                        <button type="button" wire:click="closeModal()"
                                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-all">
                                            Batal
                                        </button>
                                        <button type="submit"
                                            class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-all">
                                            {{ $isEditing ? 'Simpan' : 'Buat Baru' }}
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
