@props(['lat' => 'latitude', 'lng' => 'longitude'])

<div x-data="{
    lat: @entangle($lat),
    lng: @entangle($lng),
    map: null,
    marker: null,
    async init() {
        let initLat = parseFloat(this.lat) || -6.8694;
        let initLng = parseFloat(this.lng) || 109.0435;
        let initZoom = this.lat ? 16 : 11;

        // Ensure container is ready in DOM for modal
        await new Promise(r => setTimeout(r, 500));

        if (typeof google === 'undefined') {
            console.error('Google Maps API not loaded');
            return;
        }

        // Import necessary libraries dynamically
        const { Map } = await google.maps.importLibrary('maps');
        const { Autocomplete } = await google.maps.importLibrary('places');


        const mapOptions = {
            center: { lat: initLat, lng: initLng },
            zoom: initZoom,
            mapTypeId: 'roadmap',
            fullscreenControl: true,
            streetViewControl: true
        };

        this.map = new Map(this.$refs.mapDiv, mapOptions);

        if (this.lat && this.lng) {
            this.marker = new google.maps.Marker({
                position: { lat: initLat, lng: initLng },
                map: this.map,
                draggable: true,
            });
            
            // Keep Alpine data synced when marker is dragged
            this.marker.addListener('dragend', (e) => {
                this.lat = e.latLng.lat().toFixed(6);
                this.lng = e.latLng.lng().toFixed(6);
            });
        }

        // Click on map to move marker
        this.map.addListener('click', (e) => {
            this.lat = e.latLng.lat().toFixed(6);
            this.lng = e.latLng.lng().toFixed(6);

            if (this.marker) {
                this.marker.setPosition(e.latLng);
            } else {
                this.marker = new google.maps.Marker({
                    position: e.latLng,
                    map: this.map,
                    draggable: true,
                });
                
                this.marker.addListener('dragend', (ev) => {
                    this.lat = ev.latLng.lat().toFixed(6);
                    this.lng = ev.latLng.lng().toFixed(6);
                });
            }
        });

        // Autocomplete for search box
        const input = this.$refs.searchInput;
        const autocomplete = new Autocomplete(input, {
            fields: ['geometry', 'name'],
        });
        
        // Push control to top left
        this.map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        // Bind map bounds to autocomplete limits
        autocomplete.bindTo('bounds', this.map);

        autocomplete.addListener('place_changed', () => {
            const place = autocomplete.getPlace();
            
            if (!place.geometry || !place.geometry.location) {
                return;
            }
            
            this.lat = place.geometry.location.lat().toFixed(6);
            this.lng = place.geometry.location.lng().toFixed(6);
            
            if (this.marker) {
                this.marker.setPosition(place.geometry.location);
            } else {
                this.marker = new google.maps.Marker({
                    position: place.geometry.location,
                    map: this.map,
                    draggable: true,
                });
                this.marker.addListener('dragend', (ev) => {
                    this.lat = ev.latLng.lat().toFixed(6);
                    this.lng = ev.latLng.lng().toFixed(6);
                });
            }

            if (place.geometry.viewport) {
                this.map.fitBounds(place.geometry.viewport);
            } else {
                this.map.setCenter(place.geometry.location);
                this.map.setZoom(17);
            }
        });
    }
}">
    <label class="block text-gray-700 dark:text-slate-300 text-sm font-semibold mb-1">Koordinat Lokasi Pemasangan</label>
    <div class="grid grid-cols-2 gap-2 mb-2">
        <input type="text" x-model="lat" placeholder="Latitude" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-slate-400 focus:outline-none">
        <input type="text" x-model="lng" placeholder="Longitude" readonly class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-slate-400 focus:outline-none">
    </div>
    
    <div wire:ignore class="relative w-full">
        <input x-ref="searchInput" type="text" placeholder="Cari alamat/tempat..." class="absolute mt-2.5 ml-2.5 w-64 border border-gray-300 rounded shadow-sm px-3 py-2 text-sm z-[0] focus:ring-2 focus:ring-blue-500 outline-none">
        <div x-ref="mapDiv" class="h-64 w-full rounded-lg border border-gray-300"></div>
    </div>
    
    <p class="text-[10px] text-slate-400 mt-1 italic">Klik pada peta untuk mengubah lokasi. Marker dapat digeser. Default: Brebes, Jawa Tengah.</p>
</div>
