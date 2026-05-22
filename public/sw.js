const CACHE_NAME = "presensi-pwa-v3";

const CORE_ASSETS = [
    "/",
    "/manifest.json",
    "/favicon.ico",
    "/apple-touch-icon.png",
    "/apple-touch-icon-precomposed.png",
    "/icons/apple-touch-icon.png",
    "/icons/icon-192.png",
    "/icons/icon-512.png"
];

const NEVER_CACHE_PATTERNS = [
    /^\/api(?:\/|$)/,
    /^\/login(?:\/|$)/,
    /^\/logout(?:\/|$)/,
    /^\/register(?:\/|$)/,
    /^\/admin\/login(?:\/|$)/,
    /^\/sanctum(?:\/|$)/,
    /^\/csrf-cookie(?:\/|$)/,
    /^\/absen(?:\/|$)/,
    /^\/presensi(?:\/|$)/,
    /^\/admin\/presensi(?:\/|$)/,
    /^\/admin\/histories(?:\/|$)/,
    /^\/histories(?:\/|$)/,
    /^\/history(?:\/|$)/,
    /^\/dashboard(?:\/|$)/,
    /^\/admin\/dashboard(?:\/|$)/,
    /^\/reports(?:\/|$)/,
    /^\/admin\/reports(?:\/|$)/,
    /^\/profile(?:\/|$)/,
    /^\/push-subscriptions(?:\/|$)/,
    /^\/izin(?:\/|$)/,
    /^\/jadwal-shift(?:\/|$)/,
    /^\/tukar-shift(?:\/|$)/,
    /^\/pengumuman(?:\/|$)/,
    /^\/admin\/users(?:\/|$)/,
    /^\/admin\/biodata(?:\/|$)/,
    /^\/admin\/notifications(?:\/|$)/,
    /^\/storage(?:\/|$)/
];

const STATIC_ASSET_PATTERNS = [
    /^\/build\//,
    /^\/img\//,
    /^\/icons\//,
    /^\/face-api\//,
    /^\/favicon\.ico$/,
    /\.(?:css|js|mjs|woff2?|ttf|otf)$/i
];

const PUBLIC_PAGE_PATHS = new Set(["/"]);

function isNeverCachePath(pathname) {
    return NEVER_CACHE_PATTERNS.some((pattern) => pattern.test(pathname));
}

function isStaticAsset(request, pathname) {
    return ["style", "script", "font"].includes(request.destination)
        || STATIC_ASSET_PATTERNS.some((pattern) => pattern.test(pathname));
}

function isCacheableResponse(response) {
    return response && response.ok && response.type === "basic";
}

self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return Promise.allSettled(
                CORE_ASSETS.map((asset) => cache.add(asset))
            );
        }).then(() => self.skipWaiting())
    );
});

self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => Promise.all(
                cacheNames
                    .filter((cacheName) => cacheName !== CACHE_NAME)
                    .map((cacheName) => caches.delete(cacheName))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener("fetch", (event) => {
    const { request } = event;
    const url = new URL(request.url);

    if (request.method !== "GET" || url.origin !== self.location.origin) {
        event.respondWith(fetch(request));
        return;
    }

    if (isNeverCachePath(url.pathname)) {
        event.respondWith(fetch(request));
        return;
    }

    if (isStaticAsset(request, url.pathname)) {
        event.respondWith(cacheFirst(request));
        return;
    }

    if (request.mode === "navigate") {
        event.respondWith(networkFirstPage(request, url.pathname));
        return;
    }

    event.respondWith(fetch(request));
});

self.addEventListener("push", (event) => {
    let payload = {};

    try {
        payload = event.data ? event.data.json() : {};
    } catch (error) {
        payload = {
            title: "Aplikasi Presensi",
            body: event.data ? event.data.text() : "Ada notifikasi baru.",
        };
    }

    const title = payload.title || "Aplikasi Presensi";
    const options = {
        body: payload.body || "Ada notifikasi baru.",
        icon: payload.icon || "/icons/icon-192.png",
        badge: payload.badge || "/icons/icon-192.png",
        tag: payload.tag || "presensi-notification",
        renotify: Boolean(payload.renotify),
        data: {
            url: payload.url || "/dashboard",
        },
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener("notificationclick", (event) => {
    event.notification.close();

    const targetUrl = new URL(event.notification.data?.url || "/dashboard", self.location.origin);

    event.waitUntil(
        clients.matchAll({ type: "window", includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                const clientUrl = new URL(client.url);

                if (clientUrl.origin === targetUrl.origin && "focus" in client) {
                    client.navigate(targetUrl.href);
                    return client.focus();
                }
            }

            if (clients.openWindow) {
                return clients.openWindow(targetUrl.href);
            }

            return null;
        })
    );
});

async function cacheFirst(request) {
    const cachedResponse = await caches.match(request);

    if (cachedResponse) {
        return cachedResponse;
    }

    try {
        const networkResponse = await fetch(request);

        if (isCacheableResponse(networkResponse)) {
            const cache = await caches.open(CACHE_NAME);
            await cache.put(request, networkResponse.clone());
        }

        return networkResponse;
    } catch (error) {
        return new Response("", {
            status: 503,
            statusText: "Offline"
        });
    }
}

async function networkFirstPage(request, pathname) {
    try {
        const networkResponse = await fetch(request);

        if (PUBLIC_PAGE_PATHS.has(pathname) && isCacheableResponse(networkResponse)) {
            const cache = await caches.open(CACHE_NAME);
            await cache.put(request, networkResponse.clone());
        }

        return networkResponse;
    } catch (error) {
        const cachedResponse = await caches.match(request)
            || await caches.match("/");

        if (cachedResponse) {
            return cachedResponse;
        }

        return new Response("Aplikasi sedang offline. Silakan coba lagi saat koneksi tersedia.", {
            status: 503,
            headers: {
                "Content-Type": "text/plain; charset=utf-8"
            }
        });
    }
}
