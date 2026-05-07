<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Hotspot') }}
        </h2>
    </x-slot>

    <div class="w-full">
        <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-lg border border-gray-100 dark:border-slate-700 transition-colors">
            <div class="p-4 sm:p-6 text-gray-900 dark:text-gray-100">
                    
                    @if (session()->has('message'))
                        <div class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-400 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('message') }}</span>
                        </div>
                    @endif
                    @if (session()->has('error'))
                        <div class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-400 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-4 mb-4">
                        
                        <div class="grid grid-cols-2 lg:flex w-full lg:w-auto gap-3">
                            <div class="col-span-1">
                                <label class="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">Paket:</label>
                                <select wire:model.live="filterPackage" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Semua Paket</option>
                                    @foreach($packages_list as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">Tampilkan:</label>
                                <select wire:model.live="perPage" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="10">10</option>
                                    <option value="100">100</option>
                                    <option value="1000">1000</option>
                                    <option value="all">Semua</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row w-full lg:w-auto gap-2">
                            <button wire:click="create()" class="w-full sm:w-auto justify-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-lg transition-colors shadow-lg shadow-blue-600/20">
                                Tambah Hotspot
                            </button>

                            @if(count($selectedIds) > 0)
                                <button wire:click="requestBulkDelete" class="w-full sm:w-auto justify-center bg-red-500 hover:bg-red-600 text-white font-bold py-2.5 px-4 rounded-lg transition-colors flex items-center gap-2 shadow-lg shadow-red-500/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Hapus ({{ count($selectedIds) }})
                                </button>
                                
                                <a href="{{ route('hotspot.print-vouchers', ['ids' => implode(',', $selectedIds)]) }}" target="_blank" class="w-full sm:w-auto justify-center bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-4 rounded-lg transition-colors flex items-center gap-2 shadow-lg shadow-green-600/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                    Cetak ({{ count($selectedIds) }})
                                </a>
                            @endif
                        </div>
                    </div>

                    @if($isOpen)
                        <div class="fixed z-50 inset-0 overflow-y-auto">
                            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                <div class="fixed inset-0 transition-opacity bg-black/50" aria-hidden="true"></div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" role="dialog" aria-modal="true">
                                    <form wire:submit.prevent="store">
                                        <div class="bg-white dark:bg-slate-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 space-y-4">
                                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $hotspot_user_id ? 'Edit User Hotspot' : 'Tambah User Hotspot' }}</h3>
                                            
                                            @if(!$hotspot_user_id)
                                            <div>
                                                <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Tipe:</label>
                                                <select class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors" wire:model.live="type">
                                                    <option value="account">Akun (Username & Password)</option>
                                                    <option value="voucher">Voucher (Kode Random)</option>
                                                </select>
                                                @error('type') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                            </div>
                                            @endif

                                            @if($type === 'account')
                                            <div>
                                                <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Username:</label>
                                                <input type="text" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors" wire:model="username">
                                                @error('username') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                            </div>
                                            <div>
                                                <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Password:</label>
                                                <x-password-input id="password" wire:model="password" />
                                                @error('password') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                            </div>

                                            @else
                                            <div>
                                                <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Jumlah Voucher:</label>
                                                <input type="number" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors" wire:model="quantity" placeholder="Contoh: 10">
                                                <p class="text-[10px] text-gray-500 mt-1">* Voucher akan digenerate secara otomatis di background.</p>
                                                @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                            </div>

                                            @endif
                                            <div>
                                                <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Paket/Profile:</label>
                                                <select class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors" wire:model.live="package_id">
                                                    <option value="">Pilih Paket...</option>
                                                    @foreach($packages_list as $p)
                                                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->mikrotik_profile }})</option>
                                                    @endforeach
                                                </select>
                                                @error('package_id') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <div class="bg-gray-50 dark:bg-slate-900/50 px-4 py-3 sm:px-6 flex flex-row-reverse gap-2">
                                            <button type="submit" wire:loading.attr="disabled" wire:target="store"
                                                class="inline-flex items-center justify-center gap-2 rounded-md border border-transparent px-4 py-2 bg-blue-600 text-sm font-medium text-white shadow-sm hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                                <svg wire:loading wire:target="store" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                <span wire:loading.remove wire:target="store">Simpan</span>
                                                <span wire:loading wire:target="store">Menyimpan...</span>
                                            </button>
                                            <button wire:click="closeModal()" type="button" wire:loading.attr="disabled" wire:target="store"
                                                class="inline-flex justify-center rounded-md border border-gray-300 dark:border-slate-600 px-4 py-2 bg-white dark:bg-slate-800 text-sm font-medium text-gray-700 dark:text-slate-300 shadow-sm hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                                Batal
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Progress Bar Generate Voucher --}}
                    @if($voucherProgress)
                        <div wire:poll.1500ms="checkVoucherProgress" class="mb-4 p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/50">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span class="text-sm font-bold text-blue-700 dark:text-blue-300">Generating Voucher...</span>
                                </div>
                                <span class="text-sm font-mono font-bold text-blue-600 dark:text-blue-400">
                                    {{ $voucherProgress['current'] ?? 0 }} / {{ $voucherProgress['total'] ?? 0 }}
                                </span>
                            </div>
                            <div class="w-full bg-blue-200 dark:bg-blue-900/50 rounded-full h-3 overflow-hidden">
                                <div class="bg-gradient-to-r from-blue-500 to-indigo-500 h-3 rounded-full transition-all duration-500 ease-out"
                                     style="width: {{ ($voucherProgress['total'] ?? 1) > 0 ? round((($voucherProgress['current'] ?? 0) / ($voucherProgress['total'] ?? 1)) * 100) : 0 }}%">
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-slate-700 shadow-sm transition-colors">
                        <table class="min-w-full min-w-[850px] divide-y divide-gray-200 dark:divide-slate-700">
                            <thead class="bg-gray-50 dark:bg-slate-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left">
                                        <input type="checkbox" wire:model.live="selectAll" class="rounded dark:bg-slate-900 border-gray-300 dark:border-slate-700 text-blue-600 shadow-sm focus:ring-blue-500">
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">No</th>

                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Username</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Password</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Paket</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700 transition-colors">
                                @forelse($hotspotUsers as $idx => $hu)
                                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                        <input type="checkbox" wire:model.live="selectedIds" value="{{ $hu->id }}" class="rounded dark:bg-slate-900 border-gray-300 dark:border-slate-700 text-blue-600 shadow-sm focus:ring-blue-500">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400 text-center">{{ $hotspotUsers->firstItem() + $idx }}</td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $hu->username }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400 font-mono">{{ $hu->password }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                        <div class="font-bold text-slate-800 dark:text-white">{{ $hu->package->name ?? '-' }}</div>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400 uppercase">
                                            {{ $hu->profile }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center space-x-2">
                                        <button wire:click="edit({{ $hu->id }})" class="inline-flex items-center px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-white rounded-md transition-all shadow-sm">
                                            Edit
                                        </button>
                                        <button wire:click="requestDelete({{ $hu->id }})" class="inline-flex items-center px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded-md transition-all shadow-sm text-sm">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500 dark:text-slate-500 italic">Belum ada data user hotspot.</td>
                                </tr>
                                @endforelse

                            </tbody>
                        </table>
                        <div class="mt-4 px-4 pb-4">
                            {{ $hotspotUsers->links() }}
                        </div>
                    </div>
        </div>
    </div>

    <!-- Loading Overlay for Deletions -->
    <div wire:loading wire:target="delete, deleteSelected" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-2xl flex flex-col items-center gap-4">
            <svg class="animate-spin h-10 w-10 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm font-bold text-gray-700 dark:text-slate-300">Sedang menghapus data dari MikroTik...</span>
        </div>
    </div>

</div>

<script>
    window.addEventListener('swal:confirm', event => {
        const data = event.detail[0];
        Swal.fire({
            title: data.title,
            text: data.text,
            icon: data.type,
            showCancelButton: true,
            confirmButtonColor: data.type === 'danger' ? '#ef4444' : '#2563eb',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Lanjutkan!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-[2rem]',
                confirmButton: 'rounded-xl font-black uppercase tracking-widest text-[10px] px-6 py-3',
                cancelButton: 'rounded-xl font-black uppercase tracking-widest text-[10px] px-6 py-3'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch(data.callback, { id: data.id });
            }
        });
    });
</script>
