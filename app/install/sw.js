const CACHE_NAME = "Cache-App-v2"; // Atualize o nome do cache para indicar uma nova versão

self.addEventListener("install", event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            // Coloque os URLs dos recursos que deseja armazenar em cache
            const urlsToCache = [
                "/",
            ];

            return cache.addAll(urlsToCache);
        })
    );
});

self.addEventListener("fetch", event => {
    event.respondWith(
        caches.match(event.request).then(cachedResponse => {
            return cachedResponse || fetch(event.request).then(networkResponse => {
                const clonedResponse = networkResponse.clone();
                caches.open(CACHE_NAME).then(cache => {
                    cache.put(event.request, clonedResponse);
                });
                return networkResponse;
            }).catch(error => {
                console.error("Erro ao buscar recurso:", error);
                // Você pode lidar com erros de busca aqui, se necessário.
            });
        })
    );
});

self.addEventListener("activate", event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.filter(key => key !== CACHE_NAME)
                    .map(key => caches.delete(key))
            );
        })
    );
});
