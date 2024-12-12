var staticCacheName = "pwa-v" + new Date().getTime();
var filesToCache = [
    '/offline',
    '/css/app.css',
    '/js/app.js',
    "/storage/01JE5H38SW6JXW670XQGB28G13.png",
    "/storage/01JE5H38T714EN773M4ST5SNKY.png",
    "/storage/01JE5H38TD4S9PZMKSS7ADS3JM.png",
    "/storage/01JE5H38TMEMZ80MXJ119D09CN.png",
    "/storage/01JE5H38TV1R6T9WHRPYNRD0RE.png",
    "/storage/01JE5H38VCFXMPZ7SJJGXZK236.png",
    "/storage/01JE5H38VM4TSFCPTXEBSNNP69.png",
    "/storage/01JE5H38VWNEYKQ9HFXEXKV9MM.png"
];

// Cache on install
self.addEventListener("install", event => {
    this.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName)
            .then(cache => {
                return cache.addAll(filesToCache);
            })
    )
});

// Clear cache on activate
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(cacheName => (cacheName.startsWith("pwa-")))
                    .filter(cacheName => (cacheName !== staticCacheName))
                    .map(cacheName => caches.delete(cacheName))
            );
        })
    );
});

// Serve from Cache
self.addEventListener("fetch", event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                return response || fetch(event.request);
            })
            .catch(() => {
                return caches.match('offline');
            })
    )
});
