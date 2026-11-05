// Pijat.In - Web Push & Desktop Notification Service Worker
self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

// 1. Handle Web Push Event from Server
self.addEventListener('push', (event) => {
    let payload = {
        title: 'Pijat.In Pemberitahuan',
        body: 'Anda memiliki notifikasi operasional baru.',
        icon: '/favicon.ico',
        badge: '/favicon.ico',
        tag: 'pijatin-notification',
        data: {
            action_url: '/notifications'
        }
    };

    if (event.data) {
        try {
            const data = event.data.json();
            if (data.notification) {
                payload.title = data.notification.title || payload.title;
                payload.body = data.notification.body || payload.body;
                payload.icon = data.notification.icon || payload.icon;
                payload.tag = data.notification.tag || payload.tag;
                if (data.notification.data) {
                    payload.data = data.notification.data;
                }
            } else {
                payload.title = data.title || payload.title;
                payload.body = data.body || payload.body;
                if (data.action_url) payload.data.action_url = data.action_url;
            }
        } catch (e) {
            payload.body = event.data.text();
        }
    }

    const options = {
        body: payload.body,
        icon: payload.icon,
        badge: payload.badge,
        tag: payload.tag,
        renotify: true,
        vibrate: [200, 100, 200],
        data: payload.data
    };

    event.waitUntil(
        self.registration.showNotification(payload.title, options)
    );
});

// 2. Handle User Clicking Notification Toast on Laptop Screen
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = (event.notification.data && event.notification.data.action_url) 
        ? event.notification.data.action_url 
        : '/admin/notifications';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if ('focus' in client) {
                    client.navigate(targetUrl);
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});

