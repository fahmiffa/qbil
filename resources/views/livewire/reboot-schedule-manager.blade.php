<div>
    <div class="mt-8 p-6 bg-yellow-50/30 dark:bg-yellow-900/10 rounded-[2rem] shadow-sm transition-all hover:shadow-md">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-8 h-8 rounded-lg bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center text-yellow-600 dark:text-yellow-400 shadow-lg shadow-yellow-600/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </div>
            <label class="font-black text-xs text-yellow-600 dark:text-yellow-400 uppercase tracking-[0.2em]">Otomasi: Reboot ONU</label>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div class="flex flex-col gap-2 sm:flex-row">
                    <div class="flex-1">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Hari (cth: 5)</span>
                        <input wire:model="interval_days" type="number" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 rounded-xl text-sm px-4 py-2.5 font-bold focus:ring-2 focus:ring-yellow-500" placeholder="5">
                        @error('interval_days') <span class="text-[10px] text-red-500 mt-1 font-bold">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex-1">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Waktu WIB</span>
                        <input wire:model="time" type="time" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 rounded-xl text-sm px-4 py-2.5 font-bold focus:ring-2 focus:ring-yellow-500">
                        @error('time') <span class="text-[10px] text-red-500 mt-1 font-bold">{{ $message }}</span> @enderror
                    </div>
                </div>
                <button type="button" wire:click="addSchedule" class="w-full sm:w-auto px-6 py-2.5 bg-yellow-500 hover:bg-yellow-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl transition-all shadow-lg shadow-yellow-500/20">
                    Tambah Jadwal
                </button>
            </div>

            <div class="space-y-3">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Daftar Jadwal (Berulang)</span>
                <div class="flex flex-col gap-2">
                    @forelse($schedules as $schedule)
                    <div class="flex items-center justify-between px-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm group">
                        <div>
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-200">Setiap {{ $schedule->interval_days }} Hari</span>
                            <span class="text-[10px] text-slate-500 ml-2">Pukul {{ \Carbon\Carbon::parse($schedule->time)->format('H:i') }} WIB</span>
                            <div class="text-[9px] text-slate-400 mt-0.5">Jadwal berikutnya: {{ \Carbon\Carbon::parse($schedule->next_run_date)->translatedFormat('d F Y') }}</div>
                        </div>
                        <button type="button" wire:click="deleteSchedule({{ $schedule->id }})" class="text-slate-400 hover:text-red-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    @empty
                    <div class="text-[10px] font-bold text-slate-400 uppercase italic">Belum ada jadwal reboot.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
