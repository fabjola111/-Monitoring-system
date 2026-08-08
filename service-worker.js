const CACHE_NAME = "monitorr-cache-v1";

const urlsToCache = [
    "login.php",
    "signup.php",
    "manifest.json",
    "assets/auth.css"
];

self.addEventListener("install", event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
        .then(cache => {
            return cache.addAll(urlsToCache);
        })
    );
});

self.addEventListener("fetch", event => {
    event.respondWith(
        fetch(event.request)
        .catch(() => caches.match(event.request))
    );
});