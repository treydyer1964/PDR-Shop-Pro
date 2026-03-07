const CACHE_NAME = 'pdr-shop-pro-v1';
const OFFLINE_URL = '/offline';

// Assets to cache on install
const PRECACHE_URLS = [
    '/offline',
    '/manifest.json',
];

// Install — precache offline page
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE_URLS))
    );
    self.skipWaiting();
});

// Activate — clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            )
        )
    );
    self.clients.claim();
});

// Fetch strategy
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET and cross-origin requests
    if (request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    // Cache-first for static build assets (hashed filenames = safe to cache forever)
    if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/icons/')) {
        event.respondWith(
            caches.open(CACHE_NAME).then(async (cache) => {
                const cached = await cache.match(request);
                if (cached) return cached;
                const response = await fetch(request);
                if (response.ok) cache.put(request, response.clone());
                return response;
            })
        );
        return;
    }

    // Network-first for all page navigation — fall back to offline page
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() =>
                caches.match(OFFLINE_URL)
            )
        );
        return;
    }

    // Default: network only
    // (don't cache API responses or dynamic content)
});
