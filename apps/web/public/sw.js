// Service Worker — Sistema Fundo PWA
const CACHE_NAME = 'fundo-pwa-v2';
const STATIC_ASSETS = [
    '/',
    '/manifest.json',
    '/icons/icon.webp',
    '/icons/icon-192.webp',
    '/icons/icon-512.webp',
    '/icons/icon.svg',
    'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const req = event.request;

    // Solo peticiones GET
    if (req.method !== 'GET') return;

    // Estrategia Network-First con Fallback en Cache para páginas
    event.respondWith(
        fetch(req)
            .then((res) => {
                // Clonar respuesta y guardar en cache para uso posterior
                if (res.status === 200) {
                    const resClone = res.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(req, resClone);
                    });
                }
                return res;
            })
            .catch(() => {
                // Fallback a cache si la red falla
                return caches.match(req).then((cached) => {
                    return cached || caches.match('/');
                });
            })
    );
});
