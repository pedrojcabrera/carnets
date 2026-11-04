const STATIC_CACHE = 'carnet-static-v1';
const RUNTIME_CACHE = 'carnet-runtime-v1';
const CARNET_CACHE = 'carnet-pages-v1';
const LAST_CARNET_KEY = new URL('/__carnet-offline-last__', self.location.origin).href;

const STATIC_ASSETS = [
    '/manifest.json',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/index.php/m',
    '/m',
];

function isCarnetPage(url) {
    const pathname = new URL(url, self.location.origin).pathname;
    return /^\/(?:index\.php\/)?c\/[^/]+\/?$/.test(pathname);
}

function isCarnetStartPage(url) {
    const pathname = new URL(url, self.location.origin).pathname;
    return /^\/(?:index\.php\/)?m\/?$/.test(pathname);
}

function getCarnetPath(url) {
    return new URL(url, self.location.origin).pathname
        .replace(/^\/index\.php(?=\/)/, '')
        .replace(/\/+$/, '')
        .toLowerCase();
}

function isCacheableAsset(request, url) {
    return url.origin === self.location.origin
        || (request.destination === 'image' && ['http:', 'https:'].includes(url.protocol))
        || (request.destination === 'script' && url.hostname === 'cdn.jsdelivr.net');
}

async function cacheCarnetPage(request, response) {
    const contentType = response.headers.get('content-type') || '';
    if (
        !response.ok
        || !contentType.includes('text/html')
        || !isCarnetPage(request.url)
        || !isCarnetPage(response.url)
        || getCarnetPath(request.url) !== getCarnetPath(response.url)
    ) {
        return;
    }

    const cache = await caches.open(CARNET_CACHE);
    await cache.put(request, response.clone());

    const keys = await cache.keys();
    await Promise.all(keys
        .filter((key) => isCarnetPage(key.url) && key.url !== request.url)
        .map((key) => cache.delete(key)));

    await cache.put(LAST_CARNET_KEY, new Response(request.url, {
        headers: { 'Content-Type': 'text/plain' },
    }));
}

async function cacheCarnetAssets(assets) {
    const cache = await caches.open(RUNTIME_CACHE);

    await Promise.all(assets.map(async (asset) => {
        const url = new URL(asset.url, self.location.origin);
        const isLocalAsset = url.origin === self.location.origin;
        const isImage = asset.type === 'image' && ['http:', 'https:'].includes(url.protocol);
        const isCdnScript = asset.type === 'script'
            && url.hostname === 'cdn.jsdelivr.net'
            && url.pathname.endsWith('.js');

        if (!isLocalAsset && !isImage && !isCdnScript) {
            return;
        }

        const request = new Request(url.href, {
            mode: isLocalAsset || isCdnScript ? 'cors' : 'no-cors',
        });

        try {
            const response = await fetch(request);
            if (response.ok || response.type === 'opaque') {
                await cache.put(request, response);
            }
        } catch (error) {
            console.warn('No se pudo guardar un recurso del carnet para uso sin conexión.', error);
        }
    }));
}

self.addEventListener('install', (event) => {
    event.waitUntil((async () => {
        const cache = await caches.open(STATIC_CACHE);
        await cache.addAll(STATIC_ASSETS);
        await self.skipWaiting();
    })());
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const names = await caches.keys();
        await Promise.all(
            names
                .filter((name) => ![STATIC_CACHE, RUNTIME_CACHE, CARNET_CACHE].includes(name))
                .map((name) => caches.delete(name))
        );
        await self.clients.claim();
    })());
});

self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
        return;
    }

    if (event.data && event.data.type === 'CACHE_CURRENT_CARNET') {
        event.waitUntil((async () => {
            const url = new URL(event.data.url, self.location.origin);
            if (url.origin !== self.location.origin || !isCarnetPage(url.href)) {
                return;
            }

            const request = new Request(url.href, { credentials: 'same-origin' });
            const response = await fetch(request);
            await cacheCarnetPage(request, response);
            const assets = [
                ...(Array.isArray(event.data.images) ? event.data.images.map((url) => ({ url, type: 'image' })) : []),
                ...(Array.isArray(event.data.scripts) ? event.data.scripts.map((url) => ({ url, type: 'script' })) : []),
            ];
            await cacheCarnetAssets(assets);
        })());
    }
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (!isCacheableAsset(request, url)) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith((async () => {
            try {
                const response = await fetch(request);
                await cacheCarnetPage(request, response);
                return response;
            } catch (error) {
                const carnetCache = await caches.open(CARNET_CACHE);
                const cachedPage = await carnetCache.match(request);
                if (cachedPage) {
                    return cachedPage;
                }

                if (isCarnetStartPage(request.url)) {
                    const lastCarnet = await carnetCache.match(LAST_CARNET_KEY);
                    if (lastCarnet) {
                        const cachedCarnetUrl = await lastCarnet.text();
                        const cachedCarnet = await carnetCache.match(cachedCarnetUrl);
                        if (cachedCarnet) {
                            return cachedCarnet;
                        }
                    }
                }

                const cache = await caches.open(STATIC_CACHE);
                return (await cache.match('/index.php/m')) || (await cache.match('/m')) || Response.error();
            }
        })());
        return;
    }

    if (['style', 'script', 'image', 'font'].includes(request.destination)) {
        event.respondWith((async () => {
            const cache = await caches.open(RUNTIME_CACHE);
            const cached = await cache.match(request);

            if (cached) {
                event.waitUntil(fetch(request)
                    .then((response) => {
                        if (response && (response.ok || response.type === 'opaque')) {
                            return cache.put(request, response.clone());
                        }
                    })
                    .catch((error) => {
                        console.warn('No se pudo actualizar un recurso en caché.', error);
                    }));

                return cached;
            }

            const response = await fetch(request);
            if (response && (response.ok || response.type === 'opaque')) {
                await cache.put(request, response.clone());
            }
            return response;
        })());
    }
});
