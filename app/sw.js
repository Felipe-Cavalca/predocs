var CACHE_NAME = "Cache-App";

self.addEventListener("install", event => {
    event.waitUntil(caches.open(CACHE_NAME).then(cache => { return cache.addAll(["/"]); }))
});

self.addEventListener("fetch", event => {
    event.respondWith(caches.match(event.request).then(cachedResponse => { return cachedResponse || fetch(event.request); }));
});

self.addEventListener("activate", function activator(event) {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(keys
                .filter(key => { return key.indexOf(CACHE_NAME) !== 0; })
                .map(key => { return caches.delete(key); })
            );
        })
    );
});

