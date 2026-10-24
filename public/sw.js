// Warung Aboen Enterprise PWA Service Worker v2.0
const CACHE_NAME = 'warung-aboen-v2';

const PRECACHE_ASSETS = [
  '/',
  '/pos',
  '/manifest.json',
  '/icons/icon-192.png',
  '/icons/icon-512.png',
  'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
  'https://cdn.tailwindcss.com',
  'https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js',
  'https://cdn.jsdelivr.net/npm/chart.js'
];

// Install: Pre-cache core assets
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return Promise.allSettled(
        PRECACHE_ASSETS.map((url) =>
          cache.add(url).catch((err) => {
            console.warn('[SW] Gagal pre-cache:', url, err);
          })
        )
      );
    })
  );
  self.skipWaiting();
});

// Activate: Clean up old caches
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

// Fetch: Network-first with Cache-fallback and Runtime Caching
self.addEventListener('fetch', (event) => {
  // Only handle GET requests
  if (event.request.method !== 'GET') return;

  const url = new URL(event.request.url);

  // For HTML navigation requests (pages)
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request)
        .then((networkResponse) => {
          if (networkResponse && networkResponse.status === 200) {
            const responseClone = networkResponse.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(event.request, responseClone);
            });
          }
          return networkResponse;
        })
        .catch(async () => {
          // If offline or laptop is off, try exact match in cache
          const cachedResponse = await caches.match(event.request);
          if (cachedResponse) return cachedResponse;

          // If navigating to pos, return cached /pos
          if (url.pathname.startsWith('/pos')) {
            const cachedPos = await caches.match('/pos');
            if (cachedPos) return cachedPos;
          }

          // Otherwise return cached homepage
          const cachedHome = await caches.match('/');
          if (cachedHome) return cachedHome;

          // Fallback offline message if completely uncached
          return new Response(
            `<!DOCTYPE html>
            <html lang="id">
            <head>
              <meta charset="utf-8">
              <meta name="viewport" content="width=device-width, initial-scale=1">
              <title>Warung Aboen - Mode Offline</title>
              <style>
                body { font-family: sans-serif; background: #0f172a; color: #fff; text-align: center; padding: 40px 20px; }
                .card { background: #1e293b; border-radius: 20px; padding: 30px; max-width: 400px; margin: 0 auto; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
                h2 { color: #34d399; margin-top: 10px; }
                p { color: #94a3b8; font-size: 14px; line-height: 1.6; }
                .btn { display: inline-block; margin-top: 15px; padding: 12px 24px; background: #059669; color: white; text-decoration: none; border-radius: 12px; font-weight: bold; }
              </style>
            </head>
            <body>
              <div class="card">
                <div style="font-size: 48px;">🏪</div>
                <h2>Warung Aboen Offline</h2>
                <p>Server utama sedang tidak terhubung atau laptop belum menyala. Halaman kasir offline tersimpan dapat dibuka di bawah ini:</p>
                <a href="/pos" class="btn">Buka Kasir Offline</a>
              </div>
            </body>
            </html>`,
            { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
          );
        })
    );
    return;
  }

  // For static assets: Try cache first, then network, update cache in background
  event.respondWith(
    caches.match(event.request).then((cachedResponse) => {
      if (cachedResponse) {
        // Fetch in background to keep cache fresh
        fetch(event.request)
          .then((networkResponse) => {
            if (networkResponse && networkResponse.status === 200) {
              caches.open(CACHE_NAME).then((cache) => cache.put(event.request, networkResponse));
            }
          })
          .catch(() => {});
        return cachedResponse;
      }

      // Not in cache, fetch from network and cache it
      return fetch(event.request)
        .then((networkResponse) => {
          if (networkResponse && networkResponse.status === 200) {
            const responseClone = networkResponse.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(event.request, responseClone));
          }
          return networkResponse;
        })
        .catch(() => {
          // Return nothing or empty response if network fails
          return new Response('', { status: 408, statusText: 'Request timed out' });
        });
    })
  );
});
