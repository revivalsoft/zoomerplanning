self.addEventListener('push', async event => {
    let data = {
        title: "Nouvelle notification",
        body: "...",
        url: "/",
        id: null // ajout pour l'id unique
    };

    if (event.data) {
        try {
            const json = event.data.json();
            data.title = json.title || data.title;
            data.body = json.body || data.body;
            data.url = json.url || data.url;
            data.id = json.id || null; // récupère l'id ici
        } catch (e) {
            data.body = await event.data.text();
        }
    }

    const options = {
        body: data.body,
        // icon: '/favicon.ico',
        data: {
            url: data.url,
            id: data.id  // transmet l'id dans les données de la notif
        }
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

self.addEventListener('notificationclick', event => {
    event.notification.close();

    const notificationId = event.notification.data?.id;
    const urlToOpen = event.notification.data?.url || '/';

    // Envoie la requête au backend pour marquer la notif comme vue
    if (notificationId) {
        event.waitUntil(
            fetch('/notification/vue', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ notificationId })
            }).catch(err => {
                console.error('Erreur lors du marquage de la notification comme vue:', err);
            })
        );
    }

    event.waitUntil(
        clients.matchAll({ type: 'window' }).then(windowClients => {
            for (const client of windowClients) {
                if (client.url === urlToOpen && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});
