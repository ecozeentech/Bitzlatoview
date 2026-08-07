/**
 * Bitzlatoview service worker.
 *
 * This is a financial application — account balances, orders, and market prices must always
 * be current, so this worker deliberately does NOT cache HTML pages, API responses, or any
 * non-GET request. Its only job is:
 *   1. Cache static build assets (CSS/JS/fonts/icons) so the app shell loads instantly and
 *      works offline.
 *   2. Show a friendly offline page instead of the browser's default error when a page
 *      navigation fails because the device has no connection.
 *
 * Bump CACHE_VERSION whenever the static asset caching strategy changes, to force old caches
 * to be cleared on the next visit.
 */
const CACHE_VERSION = 'bitzlatoview-v1';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const OFFLINE_URL = '/offline.html';

const PRECACHE_URLS = [
    OFFLINE_URL,
    '/manifest.json',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter((key) => key.startsWith('bitzlatoview-') && key !== STATIC_CACHE)
                .map((key) => caches.delete(key))
        )).then(() => self.clients.claim())
    );
});

function isStaticAsset(url) {
    return url.origin === self.location.origin && (
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/icons/') ||
        url.pathname.startsWith('/fonts/') ||
        /\.(css|js|woff2?|ttf|png|jpg|jpeg|svg|webp|ico)$/.test(url.pathname)
    );
}

self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Never intercept non-GET requests (form submissions, API writes, etc.) — always hit the
    // network directly so nothing here can interfere with CSRF tokens or write operations.
    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    // Page navigations: always try the network first (so logged-in users always see live
    // data), and only fall back to the offline page if the network is genuinely unreachable.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    // Static build assets: cache-first with a background refresh, so the app shell loads
    // instantly on repeat visits and still stays up to date.
    if (isStaticAsset(url)) {
        event.respondWith(
            caches.open(STATIC_CACHE).then((cache) => cache.match(request).then((cached) => {
                const fetchPromise = fetch(request).then((response) => {
                    if (response.ok) {
                        cache.put(request, response.clone());
                    }
                    return response;
                }).catch(() => cached);

                return cached || fetchPromise;
            }))
        );
        return;
    }

    // Everything else (API calls, dynamic fragments): pass straight through to the network.
});
