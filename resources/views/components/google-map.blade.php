@props(['lat' => 'latitude', 'lng' => 'longitude'])

<div x-data="{
    lat: @entangle($lat),
    lng: @entangle($lng),
    map: null,
    marker: null,
    init() {
        window.loadGoogleMaps(async () => {
            let initLat = parseFloat(this.lat) || -6.8694;
            let initLng = parseFloat(this.lng) || 109.0435;
            let initZoom = this.lat ? 16 : 11;

            // Ensure container is ready in DOM for modal
            await new Promise(r => setTimeout(r, 500));

            // Import necessary libraries dynamically
            const { Map } = await google.maps.importLibrary('maps');

        const mapOptions = {
            center: { lat: initLat, lng: initLng },
            zoom: initZoom,
            mapTypeId: 'roadmap',
            fullscreenControl: true,
            streetViewControl: true
        };

        this.map = new Map(this.$refs.mapDiv, mapOptions);

        this.syncMarkerFromCoords = () => {
            if (!this.map || !this.lat || !this.lng) return;
            
            const flat = parseFloat(this.lat);
            const flng = parseFloat(this.lng);
            
            if (isNaN(flat) || isNaN(flng)) return;
            
            const pos = { lat: flat, lng: flng };
            
            if (this.marker) {
                this.marker.setPosition(pos);
            } else {
                this.marker = new google.maps.Marker({
                    position: pos,
                    map: this.map,
                    draggable: true,
                });
                
                this.marker.addListener('dragend', (ev) => {
                    this.lat = ev.latLng.lat().toFixed(6);
                    this.lng = ev.latLng.lng().toFixed(6);
                });
            }
            this.map.setCenter(pos);
        };

        // Initial sync if data exists
        if (this.lat && this.lng) {
            this.syncMarkerFromCoords();
        }

        // Click on map to move marker
        this.map.addListener('click', (e) => {
            this.lat = e.latLng.lat().toFixed(6);
            this.lng = e.latLng.lng().toFixed(6);
            this.syncMarkerFromCoords();
        });

        // Listen for manual updates from Laravel/Livewire
        window.addEventListener('map-updated', (e) => {
            if (e.detail.lat && e.detail.lng) {
                this.lat = e.detail.lat;
                this.lng = e.detail.lng;
                this.syncMarkerFromCoords();
            }
        });

        // Watch for Alpine data changes
        this.$watch('lat', () => this.syncMarkerFromCoords());
        this.$watch('lng', () => this.syncMarkerFromCoords());
        });
    }
} shadow-sm border border-slate-200 dark:border-slate-700 p-4 rounded-2xl">
    <label class="block text-gray-700 dark:text-slate-300 text-sm font-semibold mb-1">Koordinat Lokasi Pemasangan</label>
    <div class="grid grid-cols-2 gap-2 mb-2">
        <input type="text" x-model="lat" placeholder="Latitude" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-slate-400 focus:outline-none">
        <input type="text" x-model="lng" placeholder="Longitude" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-slate-400 focus:outline-none">
    </div>
    
    <div wire:ignore class="relative w-full">
        <div x-ref="mapDiv" class="h-64 w-full rounded-lg border border-gray-300"></div>
    </div>
    
    <p class="text-[10px] text-slate-400 mt-1 italic">Klik pada peta untuk mengubah lokasi. Marker dapat digeser. Default: Brebes, Jawa Tengah.</p>
</div>
