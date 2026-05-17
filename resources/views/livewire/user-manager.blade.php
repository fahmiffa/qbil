<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Akun') }}
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

                    <button wire:click="create()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg mb-4 transition-colors">
                        Tambah Akun
                    </button>

                    @if($isOpen)
                        <div class="fixed z-50 inset-0 overflow-y-auto">
                            <div class="flex items-center justify-center min-h-screen px-4">
                                <div class="fixed inset-0 bg-black/50" aria-hidden="true"></div>
                                <div class="relative bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all w-full max-w-lg z-10 border border-transparent dark:border-slate-700" role="dialog" aria-modal="true">
                                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-slate-700">
                                        <h3 class="text-lg font-bold text-gray-800 dark:text-white">{{ $user_id ? 'Edit Akun' : 'Tambah Akun' }}</h3>
                                        <button wire:click="closeModal()" class="text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    <form>
                                        <div class="bg-white dark:bg-slate-800 px-6 py-5 space-y-4 transition-colors">
                                            <div>
                                                <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Nama:</label>
                                                <input type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Masukan Nama" wire:model="name">
                                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                            </div>
                                            <div>
                                                <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Email:</label>
                                                <input type="email" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Masukan Email" wire:model="email">
                                                @error('email') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                            </div>
                                            <div>
                                                <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Nomor HP:</label>
                                                <input type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Masukan Nomor HP" wire:model="phone">
                                                @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                            </div>
                                            <div>
                                                <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">URI (Subdomain/Path):</label>
                                                <input type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Masukan URI" wire:model="uri">
                                                @error('uri') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                            </div>
                                            <div>
                                                 <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Role:</label>
                                                 <select class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors dark:bg-slate-900 dark:text-slate-100" wire:model="role">
                                                     <option value="1">User</option>
                                                     <option value="0">Super Admin</option>
                                                 </select>
                                                 @error('role') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                             </div>

                                             <div>
                                                 <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Izinkan Multi Router?:</label>
                                                 <select class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors dark:bg-slate-900 dark:text-slate-100" wire:model="allow_multi_router">
                                                     <option value="0">Tidak (Limit 1 Router)</option>
                                                     <option value="1">Ya (Unlimited Router)</option>
                                                 </select>
                                                 @error('allow_multi_router') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                             </div>

                                             <div>
                                                 <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Server WhatsApp:</label>
                                                 <select class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors dark:bg-slate-900 dark:text-slate-100" wire:model="whatsapp_server_id">
                                                     <option value="">-- Pilih Server WhatsApp --</option>
                                                     @foreach($whatsappServers as $server)
                                                         <option value="{{ $server->id }}">{{ $server->name }}</option>
                                                     @endforeach
                                                 </select>
                                                 @error('whatsapp_server_id') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                             </div>
                                             
                                             <div>
                                                 <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Hak Akses Fitur:</label>
                                                 <div class="grid grid-cols-2 gap-2">
                                                     @foreach($allFeatures as $f)
                                                     <label class="flex items-center space-x-2 text-sm text-gray-600 dark:text-slate-400 cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700/50 p-1.5 rounded transition-colors">
                                                         <input type="checkbox" wire:model="selectedFeatures" value="{{ $f->id }}" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                                         <span>{{ $f->name }}</span>
                                                     </label>
                                                     @endforeach
                                                 </div>
                                                 @error('selectedFeatures') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                             </div>

                                            <div>
                                                <label class="block text-gray-700 dark:text-slate-300 text-sm font-bold mb-2">Password (Kosongkan jika tidak ingin diubah):</label>
                                                <x-password-input id="password" wire:model="password" />
                                                @error('password') <span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <div class="bg-gray-50 dark:bg-slate-900/50 px-6 py-3 border-t border-gray-100 dark:border-slate-700 flex flex-row-reverse gap-2">
                                            <button wire:click.prevent="store()" type="button" class="inline-flex justify-center rounded-lg px-4 py-2 bg-blue-600 text-sm font-medium text-white shadow-sm hover:bg-blue-700 transition-colors">
                                                Simpan
                                            </button>
                                            <button wire:click="closeModal()" type="button" class="inline-flex justify-center rounded-lg border border-gray-300 dark:border-slate-600 px-4 py-2 bg-white dark:bg-slate-800 text-sm font-medium text-gray-700 dark:text-slate-300 shadow-sm hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                                                Batal
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-slate-700 shadow-sm transition-colors">
                        <table class="min-w-full min-w-[850px] divide-y divide-gray-200 dark:divide-slate-700">
                            <thead class="bg-gray-50 dark:bg-slate-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">URI</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">No. HP</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Role</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Server WA</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Multi Router</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Fitur Tersedia</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700 transition-colors">
                                @forelse($users as $idx => $user)
                                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400 text-center">{{ $users->firstItem() + $idx }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">{{ $user->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">{{ $user->uri ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">{{ $user->phone ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->role == 0 ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400' }}">
                                            {{ $user->role == 0 ? 'Super Admin' : 'User' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500 dark:text-slate-400">
                                        {{ $user->whatsappServer->name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold">
                                        @if($user->allow_multi_router || $user->role == 0)
                                            <span class="text-emerald-600 dark:text-emerald-400">Ya</span>
                                        @else
                                            <span class="text-red-600 dark:text-red-400">Tidak</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-normal text-center">
                                        @if($user->role == 0)
                                            <span class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold italic">Semua Akses (Super Admin)</span>
                                        @else
                                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                                @forelse($user->features as $feature)
                                                    <span class="px-2 py-0.5 bg-gray-100 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 text-gray-700 dark:text-gray-300 text-[11px] font-medium rounded-md whitespace-nowrap shadow-sm">
                                                        {{ $feature->name }}
                                                    </span>
                                                @empty
                                                    <span class="text-xs text-gray-400 dark:text-slate-500 italic">Belum ada fitur</span>
                                                @endforelse
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center space-x-2">
                                        <button wire:click="edit({{ $user->id }})" class="inline-flex items-center px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-white rounded-md transition-all shadow-sm">Edit</button>
                                        <button wire:click="delete({{ $user->id }})" wire:confirm="Yakin ingin menghapus akun ini?" class="inline-flex items-center px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded-md transition-all shadow-sm text-sm">Hapus</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500 dark:text-slate-500 italic">Belum ada data akun pengguna.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
        </div>
    </div>
</div>
