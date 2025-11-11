self.addEventListener('push', function (e) {
    var response = e.data.json();
    var notification = {};
    if (response.body) {
        notification.body = response.body;
    }
    if (response.icon) {
        notification.icon = response.icon;
    }
    if (response.image) {
        notification.image = response.image;
    }
    if (response.badge) {
        notification.badge = response.badge;
    }
    if (response.data) {
        if (response.data.url) {
            if (typeof notification.data == 'undefined') notification.data = {};
            notification.data.url = response.data.url;
        }
    }
    if (response.url) {
        if (typeof notification.data == 'undefined') notification.data = {};
        notification.data.url = response.url;
    }
    if (response.actions && typeof response.actions == 'object' && response.actions.length) {
        for (var i = 0; i < response.actions.length; i++) {
            if (response.actions[i].action && response.actions[i].title) {
                if (typeof notification.actions == 'undefined') notification.actions = [];
                var actionObject = {};
                actionObject.action = response.actions[i].action;
                actionObject.title = response.actions[i].title;
                if (response.actions[i].type) {
                    actionObject.type = response.actions[i].type;
                }
                if (response.actions[i].icon) {
                    actionObject.icon = response.actions[i].icon;
                }
                if (response.actions[i].placeholder) {
                    actionObject.icon = response.actions[i].placeholder;
                }
                notification.actions.push(actionObject);

            }
        }
    }

    e.waitUntil(self.registration.showNotification(response.title, notification));
});

self.addEventListener('notificationclick', function (e) {
    if (e.action && e.action == 'close') {
        return;
    }
    e.waitUntil(clients.openWindow(e.notification.data.url));
});

self.addEventListener('install', function (e) {
    /* console.log('Service worker installed.'); */
});

self.addEventListener('activate', function (e) {
    /* console.log('Service worker activated.'); */
});

self.addEventListener('fetch', function (e) {
    /* console.log('Fetch event.'); */
});
