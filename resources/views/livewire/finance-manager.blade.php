<div>
    <x-slot name="header">
        <h2 class="font-semibold text-sm md:text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Kas & Keuangan') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Messages -->
            @if (session()->has('message'))
                <div class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-400 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            @endif

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Total Income -->
                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-slate-700">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Total Pemasukan</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Expense -->
                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-slate-700">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Total Pengeluaran</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Net Profit -->
                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-slate-700">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full {{ $netProfit >= 0 ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Keuntungan Bersih</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">Rp {{ number_format($netProfit, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-lg border border-gray-100 dark:border-slate-700">
                <div class="p-6">
                    
                    <!-- Filters & Actions -->
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-4 mb-6">
                        <div class="grid grid-cols-2 lg:flex w-full lg:w-auto gap-3">
                            <div class="col-span-1">
                                <label class="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">Dari Tanggal</label>
                                <input type="date" wire:model.live="startDate" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500">
                            </div>
                            <div class="col-span-1">
                                <label class="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">Sampai Tanggal</label>
                                <input type="date" wire:model.live="endDate" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500">
                            </div>
                            <div class="col-span-1">
                                <label class="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">Tipe Transaksi</label>
                                <select wire:model.live="filterType" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500">
                                    <option value="all">Semua</option>
                                    <option value="income">Pemasukan</option>
                                    <option value="expense">Pengeluaran</option>
                                </select>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">Tampilkan</label>
                                <select wire:model.live="perPage" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500">
                                    <option value="20">20</option>
                                    <option value="100">100</option>
                                    <option value="1000">1000</option>
                                    <option value="all">Semua</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="w-full lg:w-auto mt-2 lg:mt-0">
                            <button wire:click="openModal" class="w-full lg:w-auto justify-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-lg shadow-blue-600/20 transition-all flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                Catat Transaksi
                            </button>
                        </div>
                    </div>

                    <!-- Transactions Table -->
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-slate-700 shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                            <thead class="bg-gray-50 dark:bg-slate-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Tipe</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Kategori</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Keterangan</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Nominal</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                                @forelse($transactions as $t)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-slate-300">
                                            {{ $t->transaction_date->format('d M Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($t->type === 'income')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Pemasukan</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Pengeluaran</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-slate-300 font-semibold">
                                            {{ $t->category }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-slate-400">
                                            {{ $t->description ?: '-' }}
                                            @if($t->reference_type)
                                                <div class="text-xs text-blue-500 mt-1">Otomatis dari Sistem</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-right {{ $t->type === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ $t->type === 'income' ? '+' : '-' }} Rp {{ number_format($t->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            @if(!$t->reference_type)
                                                <button wire:click="delete({{ $t->id }})" wire:confirm="Yakin ingin menghapus transaksi manual ini?" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                    Hapus
                                                </button>
                                            @else
                                                <span class="text-gray-400 text-xs italic">Sistem</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-10 text-center text-gray-500 dark:text-slate-500 italic">Belum ada transaksi pada periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $transactions->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    @if($isOpen)
    <div class="fixed z-50 inset-0 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-black/50 backdrop-blur-sm" aria-hidden="true" wire:click="closeModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100 dark:border-slate-700">
                <form wire:submit.prevent="store">
                    <div class="px-6 py-5">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Catat Transaksi Manual</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Tipe Transaksi</label>
                                <select wire:model.live="type" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2 focus:ring-blue-500">
                                    <option value="expense">Pengeluaran (Uang Keluar)</option>
                                    <option value="income">Pemasukan (Uang Masuk)</option>
                                </select>
                                @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Kategori</label>
                                <div class="relative">
                                    <input type="text" wire:model="category" list="category-options" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2 focus:ring-blue-500" placeholder="Pilih atau ketik kategori baru...">
                                    <datalist id="category-options">
                                        @if($type === 'expense')
                                            @foreach($expenseCategories as $cat)
                                                <option value="{{ $cat }}">
                                            @endforeach
                                        @else
                                            @foreach($incomeCategories as $cat)
                                                <option value="{{ $cat }}">
                                            @endforeach
                                        @endif
                                    </datalist>
                                </div>
                                @error('category') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Nominal (Rp)</label>
                                <input type="number" wire:model="amount" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2 focus:ring-blue-500" placeholder="Contoh: 150000">
                                @error('amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Tanggal & Waktu</label>
                                <input type="datetime-local" wire:model="transaction_date" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2 focus:ring-blue-500">
                                @error('transaction_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Keterangan (Opsional)</label>
                                <textarea wire:model="description" rows="3" class="w-full border border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-lg px-3 py-2 focus:ring-blue-500" placeholder="Detail transaksi..."></textarea>
                                @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 dark:bg-slate-900/50 px-6 py-4 flex justify-end gap-3 rounded-b-xl">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 font-medium transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium shadow-lg shadow-blue-600/20 transition-colors flex items-center gap-2">
                            <span wire:loading.remove wire:target="store">Simpan Transaksi</span>
                            <span wire:loading wire:target="store">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
