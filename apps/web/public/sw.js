// Service Worker — Sistema Fundo PWA (v5)
// Soporte offline-first y sincronización de ventas en campo
const CACHE_NAME = 'fundo-pwa-v5';

const PRECACHE_ASSETS = [
    '/',
    '/ventas/registrar',
    '/manifest.json',
    '/offline.html',
    '/apple-touch-icon.png',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/icon-maskable-192.png',
    '/icons/icon-maskable-512.png',
    '/icons/icon-192.webp',
    '/icons/icon-512.webp',
    '/icons/icon.svg',
    '/images/logo.png',
    '/images/logo-emblem.png',
    'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap'
];

// Instalación: Precaché de activos estáticos esenciales y activación inmediata
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS).catch((err) => {
                console.warn('[SW] Advertencia al precachear algún activo:', err);
            });
        })
    );
    self.skipWaiting();
});

// Activación: Limpieza rigurosa de versiones previas de caché y toma de control
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => {
                    console.log('[SW] Purgando caché obsoleta:', key);
                    return caches.delete(key);
                })
            );
        })
    );
    self.clients.claim();
});

// Interceptor de peticiones
self.addEventListener('fetch', (event) => {
    const req = event.request;

    // Solo interceptar peticiones GET
    if (req.method !== 'GET') return;

    const url = new URL(req.url);

    // Bypass completo para descargas de reportes en Excel (.xlsx) y rutas de exportación
    // Evita agotar el almacenamiento de CacheStorage con archivos pesados
    if (url.pathname.includes('/exportar') || url.pathname.endsWith('.xlsx')) {
        return;
    }

    // 1. Peticiones de Navegación (Páginas HTML completas)
    if (req.mode === 'navigate') {
        event.respondWith(
            fetch(req)
                .then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const copy = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(req, copy));
                    }
                    return networkResponse;
                })
                .catch(async () => {
                    // Si no hay red, buscar la página solicitada en caché
                    const cachedPage = await caches.match(req);
                    if (cachedPage) {
                        return cachedPage;
                    }
                    // Si el usuario intentaba ir a registrar venta, servir el formulario precacheado
                    if (url.pathname.includes('/ventas/registrar')) {
                        const cachedForm = await caches.match('/ventas/registrar');
                        if (cachedForm) return cachedForm;
                    }
                    // Si no estaba en caché, mostrar la pantalla de contingencia offline institucional
                    const offlinePage = await caches.match('/offline.html');
                    return offlinePage || new Response('Sin conexión a internet en campo', {
                        headers: { 'Content-Type': 'text/plain; charset=utf-8' }
                    });
                })
        );
        return;
    }

    // 2. Activos Estáticos (Imágenes, Fuentes, CSS, Iconos)
    // Estrategia: Cache First con Network Fallback
    if (
        url.pathname.startsWith('/icons/') ||
        url.pathname.startsWith('/images/') ||
        url.hostname.includes('fonts.googleapis.com') ||
        url.hostname.includes('fonts.gstatic.com')
    ) {
        event.respondWith(
            caches.match(req).then((cached) => {
                if (cached) return cached;
                return fetch(req).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const copy = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(req, copy));
                    }
                    return networkResponse;
                }).catch(() => {
                    // Retorno nulo para permitir degradación elegante
                    return new Response('', { status: 408, statusText: 'Offline Asset Unavailable' });
                });
            })
        );
        return;
    }

    // 3. Resto de peticiones (Network First con Fallback a Cache)
    event.respondWith(
        fetch(req)
            .then((networkResponse) => {
                if (networkResponse && networkResponse.status === 200) {
                    const copy = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(req, copy));
                }
                return networkResponse;
            })
            .catch(() => {
                return caches.match(req);
            })
    );
});
