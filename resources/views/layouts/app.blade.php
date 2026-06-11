<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .swal2-popup {
            font-family: 'Figtree', sans-serif !important;
            border-radius: 1.5rem !important;
        }
    </style>

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%233b82f6' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20.34 17.52a10 10 0 1 0-2.82 2.82'/%3E%3Ccircle cx='19' cy='19' r='2'/%3E%3Cpath d='m13.41 13.41 4.18 4.18'/%3E%3Ccircle cx='12' cy='12' r='2'/%3E%3C/svg%3E">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="font-sans antialiased"
    x-data="{ 
            sidebarOpen: window.innerWidth > 1024 ? (localStorage.getItem('sidebarOpen') !== 'false') : false,
            darkMode: localStorage.getItem('darkMode') === 'true'
          }"
    x-init="$watch('sidebarOpen', value => localStorage.setItem('sidebarOpen', value)); $watch('darkMode', value => localStorage.setItem('darkMode', value))"
    :class="{ 'dark': darkMode, 'bg-gray-50': !darkMode, 'bg-slate-900': darkMode }">
    <div class="h-screen flex overflow-hidden">
        <!-- Sidebar -->
        <livewire:layout.sidebar />

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 transition-all duration-300 ease-in-out"
            :class="sidebarOpen ? 'lg:ml-64' : 'lg:ml-20'">
            <!-- Top Header -->
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between px-4 z-10 shadow-sm w-full transition-colors shrink-0">
                <div class="flex items-center gap-4">
                    <!-- Sidebar Toggle Button -->
                    <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors text-gray-500 dark:text-slate-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                    </button>
                    @if (isset($header))
                    <h1 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                        {{ $header }}
                    </h1>
                    @endif
                </div>

                <!-- User Menu -->
                <div class="flex items-center gap-4">
                    <livewire:layout.top_navigation />
                </div>
            </header>

            <!-- Dynamic Toast Notifications -->
            <div x-data="{ 
                        notifications: [], 
                        add(n) { 
                            this.notifications.push({ id: Date.now(), ...n }); 
                            setTimeout(() => { this.remove(this.notifications[0]?.id) }, 5000);
                        },
                        remove(id) { 
                            this.notifications = this.notifications.filter(n => n.id !== id); 
                        }
                    }"
                @toast.window="add($event.detail)"
                class="fixed top-4 right-4 z-[9999] flex flex-col gap-3 w-full max-w-sm pointer-events-none"
                x-init="
                        @if(session('success')) add({ type: 'success', message: '{{ session('success') }}' }); @endif
                        @if(session('message')) add({ type: 'success', message: '{{ session('message') }}' }); @endif
                        @if(session('error')) add({ type: 'error', message: '{{ session('error') }}' }); @endif
                        @if(session('warning')) add({ type: 'warning', message: '{{ session('warning') }}' }); @endif
                    ">
                <template x-for="n in notifications" :key="n.id">
                    <div x-show="true"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="translate-x-full opacity-0"
                        x-transition:enter-end="translate-x-0 opacity-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="translate-x-0 opacity-100"
                        x-transition:leave-end="translate-x-full opacity-0"
                        class="pointer-events-auto flex items-center p-4 rounded-2xl shadow-2xl border backdrop-blur-md transition-all duration-500"
                        :class="{
                                'bg-emerald-500/10 border-emerald-500/20 text-emerald-700 dark:bg-emerald-600/90 dark:border-emerald-500/50 dark:text-white': n.type === 'success',
                                'bg-rose-500/10 border-rose-500/20 text-rose-700 dark:bg-rose-600/90 dark:border-rose-500/50 dark:text-white': n.type === 'error',
                                'bg-amber-500/10 border-amber-500/20 text-amber-700 dark:bg-amber-600/90 dark:border-amber-500/50 dark:text-white': n.type === 'warning',
                                'bg-blue-500/10 border-blue-500/20 text-blue-700 dark:bg-blue-600/90 dark:border-blue-500/50 dark:text-white': n.type === 'info'
                             }">
                        <div class="mr-3 shrink-0">
                            <template x-if="n.type === 'success'">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </template>
                            <template x-if="n.type === 'error'">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </template>
                            <template x-if="n.type === 'warning'">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </template>
                            <template x-if="n.type === 'info'">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </template>
                        </div>
                        <div class="flex-1 text-sm font-bold leading-relaxed" x-text="n.message"></div>
                        <button @click="remove(n.id)" class="ml-4 p-1 rounded-lg hover:bg-black/10 dark:hover:bg-white/10 text-current opacity-60 hover:opacity-100 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>

            <!-- Page Content -->
            <main class="flex-1 p-4 sm:p-6 overflow-y-auto min-w-0 transition-colors no-scrollbar">
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('scripts')

    @if(config('services.maps.provider') === 'google')
    <script>
        window.__googleMapsCallbacks = window.__googleMapsCallbacks || [];

        window.__initGoogleMaps = function() {
            while (window.__googleMapsCallbacks.length > 0) {
                const cb = window.__googleMapsCallbacks.shift();
                if (typeof cb === 'function') cb();
            }
        };

        window.loadGoogleMaps = function(callback) {
            if (window.google && window.google.maps && window.google.maps.Map) {
                if (callback) callback();
                return;
            }

            if (callback) {
                window.__googleMapsCallbacks.push(callback);
            }

            const existingScript = document.querySelector('script[src*="maps.googleapis.com"]');
            if (existingScript) {
                const checkInterval = setInterval(() => {
                    if (window.google && window.google.maps && window.google.maps.Map) {
                        clearInterval(checkInterval);
                        window.__initGoogleMaps();
                    }
                }, 500);
                return;
            }

            const script = document.createElement('script');
            script.src = "https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=marker&callback=__initGoogleMaps";
            script.async = true;
            script.defer = true;
            document.head.appendChild(script);
        }
    </script>
    @else
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        window.loadGoogleMaps = function(callback) {
            // Compatibility wrapper for Leaflet
            if (window.L) {
                if (callback) callback();
            } else {
                const checkL = setInterval(() => {
                    if (window.L) {
                        clearInterval(checkL);
                        if (callback) callback();
                    }
                }, 100);
            }
        }
    </script>
    @endif
</body>

</html>