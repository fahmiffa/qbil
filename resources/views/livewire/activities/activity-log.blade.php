<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 dark:border-slate-700">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Log Aktivitas Sistem
                    </h2>
                </div>

                <div class="space-y-4">
                    @forelse($notifications as $notification)
                        <div class="p-4 rounded-xl border border-gray-100 dark:border-slate-700/50 hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors flex gap-4">
                            <div class="flex-shrink-0 mt-1">
                                @if(($notification->data['type'] ?? '') === 'invoice')
                                    <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start">
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-tight">
                                        {{ $notification->data['title'] ?? 'Aktivitas' }}
                                    </h3>
                                    <span class="text-xs text-gray-400 dark:text-slate-500 whitespace-nowrap">
                                        {{ $notification->created_at->translatedFormat('d M Y, H:i') }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-slate-400 mt-1">
                                    {{ $notification->data['message'] ?? '' }}
                                </p>
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-300">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                    @if(!$notification->read_at)
                                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-gray-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                            </div>
                            <p class="text-gray-500 dark:text-slate-400">Belum ada catatan aktivitas.</p>
                        </div>
                    @endforelse
                </div>

            </div>
        </div>
    </div>
</div>

