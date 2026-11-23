const CACHE_NAME = 'qbil-pwa-v1.0.0';
const OFFLINE_URL = '/offline.html';

const PRECACHE_ASSETS = [
    OFFLINE_URL,
    '/manifest.json',
    '/favicon.ico',
    '/icons/icon.svg',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
    '/icons/apple-touch-icon.png'
];

// Install Event: Precache offline page & core icons
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS);
        }).then(() => {
            return self.skipWaiting();
        })
    );
});

// Activate Event: Clear older cache versions & claim clients
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((name) => {
                    if (name !== CACHE_NAME) {
                        return caches.delete(name);
                    }
                })
            );
        }).then(() => {
            return self.clients.claim();
        })
    );
});

// Fetch Event
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Only handle GET requests
    // Important: Livewire and mutations use POST/PUT/DELETE, must go straight to network
    if (request.method !== 'GET') {
        return;
    }

    // Do not intercept Livewire internal requests, Server-Sent Events, or non-HTTP protocols
    if (
        url.pathname.startsWith('/livewire/') ||
        url.pathname.includes('/onu-stream') ||
        url.pathname.includes('/stream') ||
        url.protocol.startsWith('chrome-extension')
    ) {
        return;
    }

    // 1. Navigation requests (HTML pages) -> Network-First with Offline Fallback
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .catch(() => {
                    return caches.match(OFFLINE_URL);
                })
        );
        return;
    }

    // 2. Static Assets (Vite build assets, icons, images, fonts) -> Stale While Revalidate
    const isStaticAsset = (
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/icons/') ||
        url.pathname.startsWith('/images/') ||
        url.origin.includes('fonts.bunny.net') ||
        url.origin.includes('fonts.googleapis.com') ||
        url.pathname.match(/\.(css|js|woff2|woff|ttf|png|jpg|jpeg|svg|gif|ico|webp)$/)
    );

    if (isStaticAsset) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                const fetchPromise = fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
                        const responseToCache = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, responseToCache);
                        });
                    }
                    return networkResponse;
                }).catch(() => {
                    // Network failed, nothing to update
                });

                return cachedResponse || fetchPromise;
            })
        );
        return;
    }

    // Default network fetch with error bypass
    event.respondWith(
        fetch(request).catch(() => {
            return caches.match(request);
        })
    );
});

// Listen for skip waiting messages from client
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
