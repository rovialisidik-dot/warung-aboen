// Warung Aboen Enterprise PWA Service Worker v4.0 (Offline-First Resilient)
const CACHE_NAME = 'warung-aboen-v4';

const PRECACHE_ASSETS = [
  '/',
  '/pos',
  '/kasir-hp.html',
  '/manifest.json',
  '/icons/icon-192.png',
  '/icons/icon-512.png'
];

// Install: Pre-cache core assets unconditionally
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

// Fetch Strategy
self.addEventListener('fetch', (event) => {
  // Only handle GET requests
  if (event.request.method !== 'GET') return;

  const url = new URL(event.request.url);

  // 1. FOR KASIR HP (OFFLINE-FIRST / CACHE-FIRST):
  // Always serve instantly from cache if available so it opens in 0.01s without Cloudflare errors
  if (url.pathname.endsWith('kasir-hp.html') || url.pathname.includes('kasir-hp')) {
    event.respondWith(
      caches.match('/kasir-hp.html').then((cachedResponse) => {
        // Fetch in background to keep cache fresh when online
        const networkFetch = fetch(event.request)
          .then((networkResponse) => {
            if (networkResponse && networkResponse.status === 200) {
              const clone = networkResponse.clone();
              caches.open(CACHE_NAME).then((cache) => cache.put('/kasir-hp.html', clone));
            }
            return networkResponse;
          })
          .catch(() => {});

        // Return cached immediately if present, otherwise wait for network
        return cachedResponse || networkFetch;
      })
    );
    return;
  }

  // 2. FOR ALL HTML NAVIGATION REQUESTS (Pages):
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request)
        .then((networkResponse) => {
          // If Cloudflare returns Error 1033 (status 530) or any 5xx error when tunnel is dead:
          if (!networkResponse || networkResponse.status >= 500 || networkResponse.status === 530) {
            throw new Error('Cloudflare tunnel or server unavailable: ' + (networkResponse ? networkResponse.status : 'offline'));
          }
          if (networkResponse.status === 200) {
            const responseClone = networkResponse.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(event.request, responseClone);
            });
          }
          return networkResponse;
        })
        .catch(async () => {
          // If offline or laptop is off or tunnel dead, FALLBACK TO OFFLINE KASIR HP!
          const cachedKasir = await caches.match('/kasir-hp.html');
          if (cachedKasir) return cachedKasir;

          const cachedPos = await caches.match('/pos');
          if (cachedPos) return cachedPos;

          const cachedHome = await caches.match('/');
          if (cachedHome) return cachedHome;

          // If somehow nothing is cached, show friendly fallback
          return new Response(
            `<!DOCTYPE html>
            <html lang="id">
            <head>
              <meta charset="utf-8">
              <meta name="viewport" content="width=device-width, initial-scale=1">
              <title>Warung Aboen - Mode Mandiri</title>
              <style>
                body { font-family: -apple-system, sans-serif; background: #090d16; color: #fff; text-align: center; padding: 40px 20px; }
                .card { background: #0f172a; border-radius: 20px; padding: 30px; max-width: 400px; margin: 0 auto; border: 1px solid #1e293b; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
                h2 { color: #34d399; margin-top: 10px; font-size: 20px; }
                p { color: #94a3b8; font-size: 13px; line-height: 1.6; margin-top: 10px; }
                .btn { display: inline-block; margin-top: 20px; padding: 12px 24px; background: #059669; color: white; text-decoration: none; border-radius: 12px; font-weight: bold; font-size: 14px; }
              </style>
            </head>
            <body>
              <div class="card">
                <div style="font-size: 48px;">🏪</div>
                <h2>Warung Aboen Offline</h2>
                <p>Laptop sedang tidak terhubung atau dimatikan. Anda tetap bisa berjualan seharian penuh menggunakan Kasir Mandiri HP:</p>
                <a href="/kasir-hp.html" class="btn">Buka Kasir Mandiri HP</a>
              </div>
            </body>
            </html>`,
            { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
          );
        })
    );
    return;
  }

  // 3. FOR STATIC ASSETS (Icons, Images, Manifest):
  event.respondWith(
    caches.match(event.request).then((cachedResponse) => {
      if (cachedResponse) {
        fetch(event.request)
          .then((networkResponse) => {
            if (networkResponse && networkResponse.status === 200) {
              caches.open(CACHE_NAME).then((cache) => cache.put(event.request, networkResponse));
            }
          })
          .catch(() => {});
        return cachedResponse;
      }

      return fetch(event.request)
        .then((networkResponse) => {
          if (networkResponse && networkResponse.status === 200) {
            const responseClone = networkResponse.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(event.request, responseClone));
          }
          return networkResponse;
        })
        .catch(() => {
          return new Response('', { status: 404, statusText: 'Offline Asset Not Found' });
        });
    })
  );
});
