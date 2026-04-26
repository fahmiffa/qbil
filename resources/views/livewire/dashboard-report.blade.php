<div class="space-y-6">
    <!-- Filters -->
    <div class="flex flex-wrap items-center justify-between gap-4 bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 transition-colors">
        <div class="flex items-center gap-2">
            <div class="p-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white">Laporan Keuangan</h2>
        </div>
        <div class="flex items-center gap-3">
            <select wire:model.live="month" class="bg-slate-50 dark:bg-slate-900 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors">
                @foreach(range(1, 12) as $m)
                    <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">{{ Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                @endforeach
            </select>
            <select wire:model.live="year" class="bg-slate-50 dark:bg-slate-900 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-colors">
                @foreach(range(now()->year - 2, now()->year + 1) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Pemasukan -->
        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 relative overflow-hidden group transition-colors flex flex-col justify-between">
            <div class="absolute top-0 right-0 p-3 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-12 h-12 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 14h-2v-2h2v2zm0-4h-2V7h2v5z"/></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-1">Pemasukan</p>
                <h3 class="text-xl font-black text-emerald-600 dark:text-emerald-400 leading-none">Rp {{ number_format($stats->total_paid, 0, ',', '.') }}</h3>
            </div>
            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-2 font-medium">{{ $stats->count_paid }} Terbayar</p>
        </div>

        <!-- Piutang -->
        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 relative overflow-hidden group transition-colors flex flex-col justify-between">
            <div class="absolute top-0 right-0 p-3 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-12 h-12 text-amber-600" fill="currentColor" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-1">Piutang</p>
                <h3 class="text-xl font-black text-amber-600 dark:text-amber-400 leading-none">Rp {{ number_format($stats->total_unpaid, 0, ',', '.') }}</h3>
            </div>
            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-2 font-medium">{{ $stats->count_unpaid }} Unpaid</p>
        </div>

        @foreach($serviceBreakdown as $service)
        <!-- Service Type Card -->
        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 transition-colors flex flex-col justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-1">
                    {{ auth()->user()->hasFeature('mikrotik') ? 'Layanan ' . $service->tipe : 'Layanan Paket' }}
                </p>

                <h3 class="text-lg font-black text-slate-800 dark:text-white leading-none">Rp {{ number_format($service->total, 0, ',', '.') }}</h3>
            </div>
            <div class="mt-2 flex flex-col gap-1">
                <div class="flex items-center justify-between">
                    <span class="text-[9px] font-bold text-emerald-500/80">{{ $service->active_count }} On</span>
                    <span class="text-[9px] font-bold text-red-500/80">{{ $service->tipe !== 'HOTSPOT' ? $service->suspend_count . ' Off' : '' }}</span>
                </div>
                <div class="w-full h-1 bg-slate-100 dark:bg-slate-700/50 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-500/70" style="width: {{ $service->total > 0 ? ($service->paid / $service->total) * 100 : 0 }}%"></div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Peta Lokasi Pelanggan -->
    @if(auth()->user()->hasFeature('maps'))
    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 transition-colors">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-50 dark:bg-blue-900/30 rounded-xl text-blue-600 dark:text-blue-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-white">Peta Lokasi Pelanggan</h3>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium uppercase tracking-widest">Visualisasi Geografis Cakupan Sinyal</p>
                </div>
            </div>
        </div>
        
        <div class="rounded-2xl overflow-hidden border border-slate-100 dark:border-slate-700 h-[400px]" id="map-container" wire:ignore>
            <div id="map" class="w-full h-full"></div>
        </div>
    </div>
    @endif


    <!-- Ringkasan Pembayaran -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Detail Status Pembayaran Pelanggan -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 transition-colors">
            <h3 class="text-base font-bold text-slate-800 dark:text-white mb-6">Status Pembayaran Pelanggan</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl border border-emerald-100 dark:border-emerald-900/30">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-emerald-100 dark:bg-emerald-800 rounded-lg text-emerald-600 dark:text-emerald-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Total Sudah Bayar</span>
                    </div>
                    <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ $stats->count_paid }}</span>
                </div>
                
                <div class="flex items-center justify-between p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-100 dark:border-amber-900/30">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-amber-100 dark:bg-amber-800 rounded-lg text-amber-600 dark:text-amber-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Total Belum Bayar</span>
                    </div>
                    <span class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ $stats->count_unpaid }}</span>
                </div>
            </div>
        </div>

        <!-- Detail Keuangan (Kode Unik) -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 transition-colors">
            <h3 class="text-base font-bold text-slate-800 dark:text-white mb-6">Rincian Nominal Terbayar</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between py-3 border-b border-slate-50 dark:border-slate-700">
                    <span class="text-sm text-slate-500 dark:text-slate-400">Total DPP (Tanpa Kode Unik)</span>
                    <span class="text-sm font-bold text-slate-800 dark:text-white">Rp {{ number_format($stats->total_paid_base, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between py-3 border-b border-slate-50 dark:border-slate-700">
                    <span class="text-sm text-slate-500 dark:text-slate-400">Total Akumulasi Kode Unik</span>
                    <span class="text-sm font-bold text-blue-600 dark:text-blue-400">+ Rp {{ number_format($stats->total_paid_unique, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between pt-3">
                    <span class="text-sm font-bold text-slate-800 dark:text-white">Total Akhir Diterima</span>
                    <span class="text-lg font-black text-emerald-600 dark:text-emerald-400">Rp {{ number_format($stats->total_paid, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    @if(auth()->user()->hasFeature('maps'))
    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            window.loadGoogleMaps(() => {
                const mapElement = document.getElementById('map');
                if (!mapElement) return;

                const mapOptions = {
                    center: { lat: -6.9400, lng: 108.9300 }, // Default center
                    zoom: 10,
                    mapTypeId: 'roadmap'
                };

                const map = new google.maps.Map(mapElement, mapOptions);
                const bounds = new google.maps.LatLngBounds();
                const customers = @json($customersForMap ?? []);

                let hasMarkers = false;

                customers.forEach(customer => {
                    if (customer.latitude && customer.longitude) {
                        const position = { 
                            lat: parseFloat(customer.latitude), 
                            lng: parseFloat(customer.longitude) 
                        };
                        
                        // Marker color based on status
                        let pinColor = customer.status === 'active' ? '#10b981' : '#ef4444';
                        
                        const marker = new google.maps.Marker({
                            position: position,
                            map: map,
                            title: customer.name,
                            icon: {
                                path: google.maps.SymbolPath.CIRCLE,
                                scale: 8,
                                fillColor: pinColor,
                                fillOpacity: 1,
                                strokeColor: '#ffffff',
                                strokeWeight: 2,
                            }
                        });

                        bounds.extend(position);
                        hasMarkers = true;

                        const infoWindow = new google.maps.InfoWindow({
                            content: `
                                <div style="padding: 5px;">
                                    <h4 style="font-weight: bold; margin-bottom: 5px; color: #1e293b;">${customer.name}</h4>
                                    <p style="margin: 0; font-size: 12px; color: #64748b;">${customer.address || 'Tanpa alamat'}</p>
                                    <p style="margin: 0; font-size: 12px; font-weight: bold; color: ${pinColor}; mt-1">${customer.status.toUpperCase()}</p>
                                </div>
                            `
                        });

                        marker.addListener('click', () => {
                            infoWindow.open(map, marker);
                        });
                    }
                });

                if (hasMarkers) {
                    map.fitBounds(bounds);
                }
            });
        });
    </script>
    @endpush
    @endif

</div>
</div>
