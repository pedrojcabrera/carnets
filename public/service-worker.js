/**
 * Service Worker — Carnet Digital PWA
 *
 * Estrategia: Cache First para assets estáticos,
 * Network First para datos dinámicos del carnet.
 */

const CACHE_NAME = 'carnet-v3';
const SCOPE_PATH = new URL(self.registration.scope).pathname.replace(/\/$/, '');

function withScope(path) {
    return `${SCOPE_PATH}${path}`;
}

// No incluir el propio service-worker.js (bloquea actualizaciones)
// No incluir rutas dinámicas como /carnet/base.png (generadas por el servidor)
const STATIC_ASSETS = [
    withScope('/m'),
    withScope('/carnet'),
    withScope('/icons/icon-192.png'),
    withScope('/icons/icon-512.png'),
    withScope('/manifest.json'),
];

// ── Instalación: precachear assets estáticos ──────────────
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(async (cache) => {
            await Promise.allSettled(
                STATIC_ASSETS.map(async (asset) => {
                    const response = await fetch(asset, { cache: 'no-cache' });
                    if (!response || response.status >= 400) {
                        console.warn(`[SW] No se pudo cachear ${asset}: ${response?.status}`);
                        return;
                    }
                    await cache.put(asset, response.clone());
                })
            );
        }).then(() => self.skipWaiting())
    );
});

// ── Mensaje: forzar activación inmediata desde el cliente ─
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

// ── Activación: limpiar caches antiguas ───────────────────
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            );
        })
    );
    self.clients.claim();
});

// ── Fetch: estrategia según tipo de petición ─────────────
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);
    const path = url.pathname.startsWith(SCOPE_PATH)
        ? url.pathname.slice(SCOPE_PATH.length) || '/'
        : url.pathname;

    // Rutas del carnet → Network First (datos siempre frescos)
    if (
        path.startsWith('/c/')
        || path.startsWith('/carnet/ver/')
        || path.startsWith('/carnet/dni/')
        || path === '/m'
        || path === '/carnet'
    ) {
        event.respondWith(networkFirst(event.request));
        return;
    }

    // Assets estáticos → Cache First
    event.respondWith(cacheFirst(event.request));
});

// ── Helpers ───────────────────────────────────────────────

async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) return cached;

    try {
        const response = await fetch(request);
        if (response && response.status === 200) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        return new Response('Sin conexión', { status: 503 });
    }
}

async function networkFirst(request) {
    try {
        const response = await fetch(request);
        if (response && response.status === 200) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        const cached = await caches.match(request);
        return cached || new Response('Sin conexión', { status: 503 });
    }
}
