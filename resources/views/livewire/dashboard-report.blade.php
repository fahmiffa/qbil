<div class="space-y-6">
    <!-- Filters -->
    <div class="flex flex-wrap items-center justify-between gap-4 bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 transition-colors">
        <div class="flex items-center gap-2">
            <div class="p-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
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
            <button wire:click="toggleNominal" class="p-2.5 bg-slate-50 dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors shadow-sm" title="{{ $showNominal ? 'Sembunyikan Nominal' : 'Tampilkan Nominal' }}">
                @if($showNominal)
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                @else
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                </svg>
                @endif
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Pemasukan -->
        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 relative overflow-hidden group transition-colors flex flex-col justify-between">
            <div class="absolute top-0 right-0 p-3 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-12 h-12 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 14h-2v-2h2v2zm0-4h-2V7h2v5z" />
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-1">Sudah bayar</p>
                <h3 class="text-xl font-black text-emerald-600 dark:text-emerald-400 leading-none">
                    @if($showNominal)
                    Rp {{ number_format($stats->total_paid, 0, ',', '.') }}
                    @else
                    Rp ●●●●●●
                    @endif
                </h3>
            </div>
            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-2 font-medium">{{ $stats->count_paid }} Terbayar</p>
        </div>

        <!-- Piutang -->
        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 relative overflow-hidden group transition-colors flex flex-col justify-between">
            <div class="absolute top-0 right-0 p-3 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-12 h-12 text-amber-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z" />
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-1">Belum Bayar</p>
                <h3 class="text-xl font-black text-amber-600 dark:text-amber-400 leading-none">
                    @if($showNominal)
                    Rp {{ number_format($stats->total_unpaid, 0, ',', '.') }}
                    @else
                    Rp ●●●●●●
                    @endif
                </h3>
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

                <h3 class="text-lg font-black text-slate-800 dark:text-white leading-none">
                    @if($showNominal)
                    Rp {{ number_format($service->total, 0, ',', '.') }}
                    @else
                    Rp ●●●●●●
                    @endif
                </h3>
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
    @if(auth()->user()->hasFeature('map'))
    @php
    $provider = config('services.maps.provider', 'google');
    @endphp
    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-700 p-6 transition-colors"
        data-customers="{{ json_encode($mapData ?? []) }}"
        data-assets="{{ json_encode($assetsData ?? []) }}"
        x-data="{
            provider: '{{ $provider }}',
            init() {
                window.loadGoogleMaps(() => {
                    const mapElement = this.$refs.mapDiv;
                    if (!mapElement) return;

                    let customers = [];
                    let assets = [];
                    try {
                        customers = JSON.parse(this.$el.dataset.customers || '[]');
                        assets = JSON.parse(this.$el.dataset.assets || '[]');
                    } catch (e) {
                        console.error('Failed to parse map data', e);
                    }

                    if (this.provider === 'google') {
                        const mapOptions = {
                            center: { lat: -6.9400, lng: 108.9300 },
                            zoom: 10,
                            mapTypeId: 'roadmap'
                        };

                        const map = new google.maps.Map(mapElement, mapOptions);
                        const bounds = new google.maps.LatLngBounds();
                        let hasMarkers = false;
                        const assetMap = {};
                        const polylines = [];

                        assets.forEach(asset => {
                            if (asset.latitude && asset.longitude) {
                                const position = { lat: parseFloat(asset.latitude), lng: parseFloat(asset.longitude) };
                                assetMap[asset.id] = position;
                                
                                const assetSvg = `<svg xmlns='http://www.w3.org/2000/svg' width='32' height='40' viewBox='0 0 32 40'>
                                    <path d='M16 2C8.268 2 2 8.268 2 16c0 9.333 14 22 14 22s14-12.667 14-22C30 8.268 23.732 2 16 2z' fill='rgba(0,0,0,0.2)' transform='translate(0, 2)'/>
                                    <path d='M16 2C8.268 2 2 8.268 2 16c0 9.333 14 22 14 22s14-12.667 14-22C30 8.268 23.732 2 16 2z' fill='white'/>
                                    <circle cx='16' cy='16' r='11' fill='#3b82f6'/>
                                    <svg x='7' y='7' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'>
                                        <path d='M22 7.7c0-.6-.4-1.2-.8-1.5l-6.3-3.9a1.72 1.72 0 0 0-1.7 0l-10.3 6c-.5.2-.9.8-.9 1.4v6.6c0 .5.4 1.2.8 1.5l6.3 3.9a1.72 1.72 0 0 0 1.7 0l10.3-6c.5-.3.9-1 .9-1.5Z'/>
                                        <path d='M10 21.9V14L2.1 9.1'/>
                                        <path d='m10 14 11.9-6.9'/>
                                        <path d='M14 19.8v-8.1'/>
                                        <path d='M18 17.5V9.4'/>
                                    </svg>
                                </svg>`;

                                const marker = new google.maps.Marker({
                                    position: position,
                                    map: map,
                                    title: asset.name,
                                    icon: {
                                        url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(assetSvg),
                                        scaledSize: new google.maps.Size(32, 40),
                                        anchor: new google.maps.Point(16, 40),
                                    },
                                    zIndex: 100
                                });

                                bounds.extend(position);
                                hasMarkers = true;

                                const infoWindow = new google.maps.InfoWindow({
                                    content: `<div style='padding: 5px;'><h4 style='font-weight: bold; margin-bottom: 5px; color: #1e293b;'>${asset.name}</h4><p style='margin: 0; font-size: 12px; font-weight: bold; color: #3b82f6; margin-top: 2px;'>ASSET - ${asset.category || 'N/A'}</p></div>`
                                });

                                marker.addListener('click', () => infoWindow.open(map, marker));
                            }
                        });

                        customers.forEach(customer => {
                            if (customer.latitude && customer.longitude) {
                                const position = { lat: parseFloat(customer.latitude), lng: parseFloat(customer.longitude) };
                                let pinColor = customer.status === 'active' ? '#047857' : '#ef4444';
                                
                                const customerSvg = `<svg xmlns='http://www.w3.org/2000/svg' width='32' height='40' viewBox='0 0 32 40'>
                                    <path d='M16 2C8.268 2 2 8.268 2 16c0 9.333 14 22 14 22s14-12.667 14-22C30 8.268 23.732 2 16 2z' fill='rgba(0,0,0,0.2)' transform='translate(0, 2)'/>
                                    <path d='M16 2C8.268 2 2 8.268 2 16c0 9.333 14 22 14 22s14-12.667 14-22C30 8.268 23.732 2 16 2z' fill='white'/>
                                    <circle cx='16' cy='16' r='11' fill='${pinColor}'/>
                                    <svg x='7' y='7' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'>
                                        <path d='m15 20 3-3h2a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h2l3 3z'/>
                                        <path d='M6 8v1'/>
                                        <path d='M10 8v1'/>
                                        <path d='M14 8v1'/>
                                        <path d='M18 8v1'/>
                                    </svg>
                                </svg>`;

                                const marker = new google.maps.Marker({
                                    position: position,
                                    map: map,
                                    title: customer.name,
                                    icon: {
                                        url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(customerSvg),
                                        scaledSize: new google.maps.Size(32, 40),
                                        anchor: new google.maps.Point(16, 40),
                                    }
                                });

                                bounds.extend(position);
                                hasMarkers = true;

                                if (customer.asset_id && assetMap[customer.asset_id]) {
                                    const lineSymbol = { path: 'M 0,-1 0,1', strokeOpacity: 1, strokeColor: '#f97316', scale: 3 };
                                    const polyline = new google.maps.Polyline({
                                        path: [assetMap[customer.asset_id], position],
                                        map: map,
                                        strokeOpacity: 0,
                                        icons: [{ icon: lineSymbol, offset: '0', repeat: '15px' }],
                                        geodesic: true
                                    });
                                    polylines.push(polyline);
                                }

                                const infoWindow = new google.maps.InfoWindow({
                                    content: `<div style='padding: 5px;'><h4 style='font-weight: bold; margin-bottom: 5px; color: #1e293b;'>${customer.name}</h4><p style='margin: 0; font-size: 12px; font-weight: bold; color: ${pinColor}; margin-top: 2px;'>${customer.status.toUpperCase()}</p></div>`
                                });

                                marker.addListener('click', () => infoWindow.open(map, marker));
                            }
                        });

                        if (polylines.length > 0) {
                            let count = 0;
                            window.setInterval(() => {
                                count = (count + 1) % 200;
                                polylines.forEach(pl => {
                                    const icons = pl.get('icons');
                                    if(icons && icons.length > 0) {
                                        icons[0].offset = (count / 2) + 'px';
                                        pl.set('icons', icons);
                                    }
                                });
                            }, 50);
                        }

                        if (hasMarkers) map.fitBounds(bounds);

                    } else {
                        // Leaflet Implementation
                        const map = L.map(mapElement).setView([-6.9400, 108.9300], 10);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(map);

                        const markers = [];
                        const assetMap = {};
                        const polylines = [];

                        assets.forEach(asset => {
                            if (asset.latitude && asset.longitude) {
                                const position = [parseFloat(asset.latitude), parseFloat(asset.longitude)];
                                assetMap[asset.id] = position;
                                
                                const assetSvg = `<svg xmlns='http://www.w3.org/2000/svg' width='32' height='40' viewBox='0 0 32 40'>
                                    <path d='M16 2C8.268 2 2 8.268 2 16c0 9.333 14 22 14 22s14-12.667 14-22C30 8.268 23.732 2 16 2z' fill='rgba(0,0,0,0.2)' transform='translate(0, 2)'/>
                                    <path d='M16 2C8.268 2 2 8.268 2 16c0 9.333 14 22 14 22s14-12.667 14-22C30 8.268 23.732 2 16 2z' fill='white'/>
                                    <circle cx='16' cy='16' r='11' fill='#3b82f6'/>
                                    <svg x='7' y='7' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'>
                                        <path d='M22 7.7c0-.6-.4-1.2-.8-1.5l-6.3-3.9a1.72 1.72 0 0 0-1.7 0l-10.3 6c-.5.2-.9.8-.9 1.4v6.6c0 .5.4 1.2.8 1.5l6.3 3.9a1.72 1.72 0 0 0 1.7 0l10.3-6c.5-.3.9-1 .9-1.5Z'/>
                                        <path d='M10 21.9V14L2.1 9.1'/>
                                        <path d='m10 14 11.9-6.9'/>
                                        <path d='M14 19.8v-8.1'/>
                                        <path d='M18 17.5V9.4'/>
                                    </svg>
                                </svg>`;

                                const icon = L.divIcon({
                                    html: assetSvg,
                                    className: '',
                                    iconSize: [32, 40],
                                    iconAnchor: [16, 40]
                                });

                                const marker = L.marker(position, { icon: icon }).addTo(map)
                                    .bindPopup(`<div style='padding: 5px;'><h4 style='font-weight: bold; margin-bottom: 5px; color: #1e293b;'>${asset.name}</h4><p style='margin: 0; font-size: 12px; font-weight: bold; color: #3b82f6; margin-top: 2px;'>ASSET - ${asset.category || 'N/A'}</p></div>`);
                                
                                markers.push(position);
                            }
                        });

                        customers.forEach(customer => {
                            if (customer.latitude && customer.longitude) {
                                const position = [parseFloat(customer.latitude), parseFloat(customer.longitude)];
                                let pinColor = customer.status === 'active' ? '#047857' : '#ef4444';
                                
                                const customerSvg = `<svg xmlns='http://www.w3.org/2000/svg' width='32' height='40' viewBox='0 0 32 40'>
                                    <path d='M16 2C8.268 2 2 8.268 2 16c0 9.333 14 22 14 22s14-12.667 14-22C30 8.268 23.732 2 16 2z' fill='rgba(0,0,0,0.2)' transform='translate(0, 2)'/>
                                    <path d='M16 2C8.268 2 2 8.268 2 16c0 9.333 14 22 14 22s14-12.667 14-22C30 8.268 23.732 2 16 2z' fill='white'/>
                                    <circle cx='16' cy='16' r='11' fill='${pinColor}'/>
                                    <svg x='7' y='7' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'>
                                        <path d='m15 20 3-3h2a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h2l3 3z'/>
                                        <path d='M6 8v1'/>
                                        <path d='M10 8v1'/>
                                        <path d='M14 8v1'/>
                                        <path d='M18 8v1'/>
                                    </svg>
                                </svg>`;

                                const icon = L.divIcon({
                                    html: customerSvg,
                                    className: '',
                                    iconSize: [32, 40],
                                    iconAnchor: [16, 40]
                                });

                                L.marker(position, { icon: icon }).addTo(map)
                                    .bindPopup(`<div style='padding: 5px;'><h4 style='font-weight: bold; margin-bottom: 5px; color: #1e293b;'>${customer.name}</h4><p style='margin: 0; font-size: 12px; font-weight: bold; color: ${pinColor}; margin-top: 2px;'>${customer.status.toUpperCase()}</p></div>`);
                                
                                markers.push(position);

                                if (customer.asset_id && assetMap[customer.asset_id]) {
                                    const polyline = L.polyline([assetMap[customer.asset_id], position], {
                                        color: '#f97316',
                                        weight: 3,
                                        dashArray: '10, 10',
                                        dashOffset: '0'
                                    }).addTo(map);
                                    polylines.push(polyline);
                                }
                            }
                        });

                        // Animate Leaflet polylines
                        if (polylines.length > 0) {
                            let offset = 0;
                            window.setInterval(() => {
                                offset = (offset + 1) % 20;
                                polylines.forEach(pl => {
                                    pl.setStyle({ dashOffset: (-offset).toString() });
                                });
                            }, 50);
                        }

                        if (markers.length > 0) map.fitBounds(L.latLngBounds(markers));
                    }
                });
            }
         }">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-50 dark:bg-blue-900/30 rounded-xl text-blue-600 dark:text-blue-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-white">Peta Lokasi Pelanggan</h3>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium uppercase tracking-widest">Visualisasi Geografis Cakupan Sinyal</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl overflow-hidden border border-slate-100 dark:border-slate-700 h-[400px]" wire:ignore>
            <div x-ref="mapDiv" class="w-full h-full"></div>
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
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Total Sudah Bayar</span>
                    </div>
                    <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ $stats->count_paid }}</span>
                </div>

                <div class="flex items-center justify-between p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-100 dark:border-amber-900/30">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-amber-100 dark:bg-amber-800 rounded-lg text-amber-600 dark:text-amber-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
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
                    <span class="text-sm font-bold text-slate-800 dark:text-white">
                        @if($showNominal)
                        Rp {{ number_format($stats->total_paid_base, 0, ',', '.') }}
                        @else
                        Rp ●●●●●●
                        @endif
                    </span>
                </div>
                <div class="flex items-center justify-between py-3 border-b border-slate-50 dark:border-slate-700">
                    <span class="text-sm text-slate-500 dark:text-slate-400">Total Akumulasi Kode Unik</span>
                    <span class="text-sm font-bold text-blue-600 dark:text-blue-400">
                        @if($showNominal)
                        + Rp {{ number_format($stats->total_paid_unique, 0, ',', '.') }}
                        @else
                        + Rp ●●●
                        @endif
                    </span>
                </div>
                <div class="flex items-center justify-between pt-3">
                    <span class="text-sm font-bold text-slate-800 dark:text-white">Total Akhir Diterima</span>
                    <span class="text-lg font-black text-emerald-600 dark:text-emerald-400">
                        @if($showNominal)
                        Rp {{ number_format($stats->total_paid, 0, ',', '.') }}
                        @else
                        Rp ●●●●●●
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

</div>
</div>