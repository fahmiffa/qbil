<div>
    <!-- Header & Action -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Data Diskon</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Kelola diskon untuk paket hotspot portal publik</p>
        </div>
        <button wire:click="openModal" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-black px-4 py-2.5 rounded-xl transition-all shadow-lg shadow-blue-600/20 uppercase tracking-widest active:scale-95 flex items-center gap-2">
            <svg class="w-4 h-4 text-white font-bold" stroke-width="2" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Diskon
        </button>
    </div>

    @if (session()->has('message'))
    <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/20 border-2 border-emerald-100 dark:border-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-2xl flex items-center gap-3 shadow-lg shadow-emerald-500/5">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
        </svg>
        <span class="text-xs font-black uppercase tracking-tight">{{ session('message') }}</span>
    </div>
    @endif

    <!-- Table -->
    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-50 dark:border-slate-800/50">
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Nama Diskon</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Paket / Harga</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Potongan</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Kuota</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                    @forelse($discounts as $discount)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-5">
                            <span class="text-sm font-black text-slate-900 dark:text-white tracking-tight">{{ $discount->name }}</span>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex flex-col">
                                <span class="text-sm font-black text-slate-700 dark:text-slate-300">{{ $discount->package->name ?? 'N/A' }}</span>
                                <span class="text-[10px] font-bold text-slate-400">Rp {{ number_format($discount->package->price ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            @if($discount->type === 'percentage')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border border-blue-200 dark:border-blue-800">{{ $discount->amount + 0 }}%</span>
                            @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">Rp {{ number_format($discount->amount, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-5">
                            <span class="text-sm font-black text-slate-900 dark:text-white tracking-tight">{{ $discount->quota }}</span>
                        </td>
                        <td class="px-6 py-5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="edit({{ $discount->id }})" class="p-2 text-slate-400 hover:text-blue-600 transition-colors bg-slate-50 hover:bg-blue-50 dark:bg-slate-800 dark:hover:bg-blue-900/30 rounded-xl">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button wire:click="delete({{ $discount->id }})" wire:confirm="Yakin ingin menghapus diskon ini?" class="p-2 text-slate-400 hover:text-red-600 transition-colors bg-slate-50 hover:bg-red-50 dark:bg-slate-800 dark:hover:bg-red-900/30 rounded-xl">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-20 text-center">
                            <p class="text-slate-500 font-bold uppercase tracking-widest text-[10px]">Belum ada data diskon</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($discounts->hasPages())
        <div class="px-6 py-4 border-t border-slate-50 dark:border-slate-800/50">
            {{ $discounts->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Form -->
    @if($isModalOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/40 dark:bg-slate-950/60 backdrop-blur-sm" wire:click="closeModal"></div>

        <!-- Modal Content -->
        <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] w-full max-w-lg relative shadow-2xl p-8 border border-slate-100 dark:border-slate-800 animate-in fade-in zoom-in duration-200">

            <!-- Close Button -->
            <button wire:click="closeModal" class="absolute top-6 right-6 p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Header -->
            <div class="mb-8">
                <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
                    {{ $editingId ? 'Edit Diskon' : 'Tambah Diskon Baru' }}
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 font-medium">Buat promo atau potongan harga untuk paket hotspot.</p>
            </div>

            <!-- Form -->
            <form wire:submit.prevent="{{ $editingId ? 'update' : 'store' }}" class="space-y-6">
                <!-- Name -->
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Nama / Label Promo</label>
                    <input type="text" wire:model="name" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl block p-4 text-sm font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition-shadow placeholder:text-slate-300 dark:placeholder:text-slate-600" placeholder="Contoh: Diskon Kemerdekaan" required>
                    @error('name') <span class="text-xs text-red-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Package Dropdown -->
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pilih Paket Hotspot</label>
                    <select wire:model.live="package_id" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl block p-4 text-sm font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition-shadow appearance-none" required>
                        <option value="">-- Pilih Paket --</option>
                        @foreach($packages as $pkg)
                        <option value="{{ $pkg->id }}">{{ $pkg->name }} - Rp {{ number_format($pkg->price, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                    @error('package_id') <span class="text-xs text-red-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Type -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tipe Diskon</label>
                        <select wire:model.live="type" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl block p-4 text-sm font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition-shadow appearance-none">
                            <option value="nominal">Nominal (Rp)</option>
                            <option value="percentage">Persentase (%)</option>
                        </select>
                        @error('type') <span class="text-xs text-red-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Amount -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            Nilai Diskon ({{ $type === 'percentage' ? '%' : 'Rp' }})
                        </label>
                        <input type="number" step="0.01" wire:model.live.debounce.500ms="amount" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl block p-4 text-sm font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition-shadow placeholder:text-slate-300 dark:placeholder:text-slate-600" placeholder="{{ $type === 'percentage' ? '10' : '5000' }}" required>
                        @error('amount') <span class="text-xs text-red-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Quota -->
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Kuota Diskon (Baris)</label>
                    <input type="number" wire:model="quota" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl block p-4 text-sm font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition-shadow placeholder:text-slate-300 dark:placeholder:text-slate-600" placeholder="Contoh: 100" required>
                    <p class="text-[10px] text-slate-400 font-bold italic">Berapa kali diskon ini dapat digunakan.</p>
                    @error('quota') <span class="text-xs text-red-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Preview Box -->
                @if($package_id)
                <div class="bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-100 dark:border-blue-900/30 p-4 rounded-2xl space-y-2">
                    <p class="text-[10px] font-black text-blue-400 uppercase tracking-widest">Preview Harga</p>
                    <div class="flex justify-between items-center text-sm">
                        <span class="font-bold text-slate-600 dark:text-slate-400">Harga Awal:</span>
                        <span class="font-bold text-slate-600 dark:text-slate-400 line-through">Rp {{ number_format($selectedPackagePrice, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="font-bold text-slate-600 dark:text-slate-400">Potongan:</span>
                        <span class="font-bold text-red-500">
                            - {{ $type === 'percentage' ? ($amount + 0) . '%' : 'Rp ' . number_format((float)($amount ?? 0), 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="pt-2 mt-2 border-t border-blue-200/50 dark:border-blue-800/50 flex justify-between items-center">
                        <span class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-widest">Harga Setelah Diskon:</span>
                        <span class="text-lg font-black text-emerald-600 dark:text-emerald-400">Rp {{ number_format($finalPricePreview, 0, ',', '.') }}</span>
                    </div>
                </div>
                @endif

                <!-- Actions -->
                <div class="pt-4 flex items-center justify-end gap-3">
                    <button type="button" wire:click="closeModal" class="px-6 py-4 rounded-2xl text-xs font-black text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 transition-all uppercase tracking-widest">
                        Batal
                    </button>
                    <button type="submit" class="px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-xs font-black transition-all shadow-xl shadow-blue-600/30 uppercase tracking-widest active:scale-95 flex items-center justify-center gap-2 w-full sm:w-auto">
                        <span wire:loading.remove wire:target="{{ $editingId ? 'update' : 'store' }}">Simpan</span>
                        <div wire:loading.flex wire:target="{{ $editingId ? 'update' : 'store' }}" class="items-center gap-2">
                            <svg class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Loading</span>
                        </div>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>