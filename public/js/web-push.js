(function () {
    var applicationKey = 'BLUwHoGQ542khLboEeGHKlCxfPsrzDtiOEEkJdrCv6VcBXXAEmf8W3M7JrZfJnp6Q1I2yGDm661DA_JzZicVW9Q';
    var apiEndpoint = '/web-push/subscribe';
    var devMode = true;
    var webPushBtns = document.querySelectorAll('.web-push-btn');
    var webPushBtnsLength = webPushBtns ? webPushBtns.length : 0;

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js');

        function requestPermission() {
            if(Notification.permission === 'denied'){
                alert('Notifications are blocked. Please open your browser preferences or click on the lock near the address bar to change your notification preferences.');
                return;
            }
            Notification.requestPermission().then(function (permission) {
                if (permission === 'granted') {
                    if (webPushBtns) {
                        for (var i = 0; i < webPushBtnsLength; i++) {
                            webPushBtns[i].classList.add('hidden');
                        }
                    }
                    navigator.serviceWorker.ready.then(function (sw) {
                        sw.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: applicationKey,
                        }).then(function (subscription) {
                            subscription = JSON.stringify(subscription);
                            var formData = new FormData();
                            formData.append('token', subscription);
                            axios.post(apiEndpoint, formData).then(function(res){
                                devMode && console.log(res);
                                sessionStorage.setItem('tryWebPushToken', 'no');
                            }).catch(function(err) {
                                devMode && console.log(err);
                                sessionStorage.setItem('tryWebPushToken', 'yes');
                            });
                        });
                    });
                }
            });
        }

        function silentPermission() {
            var tryAgainWebPushToken = sessionStorage.getItem('tryAgainWebPushToken');
            if (tryAgainWebPushToken !== 'no') {
                sessionStorage.setItem('tryAgainWebPushToken', 'no');
                devMode && console.log('WEB PUSH => silent asking permission token');
                Notification.requestPermission().then(function (permission) {
                    if (permission === 'granted') {
                        navigator.serviceWorker.ready.then(function (sw) {
                            sw.pushManager.subscribe({
                                userVisibleOnly: true,
                                applicationServerKey: applicationKey,
                            }).then(function (subscription) {
                                subscription = JSON.stringify(subscription);
                                var formData = new FormData();
                                formData.append('token', subscription);
                                axios.post(apiEndpoint, formData).then(function(res){
                                    devMode && console.log('WEB PUSH => silent permission saved');
                                    sessionStorage.setItem('tryAgainWebPushToken', 'no');
                                }).catch(function(err){
                                    devMode && console.log('WEB PUSH => silent permission not saved');
                                    sessionStorage.setItem('tryAgainWebPushToken', 'yes');
                                });
                            });
                        });
                    }
                });
            }
        }


        if (Notification.permission !== 'granted') {
            devMode && console.log('WEB PUSH => permission not granted');
            if (webPushBtns) {
                for (var i = 0; i < webPushBtnsLength; i++) {
                    webPushBtns[i].classList.remove('hidden');
                    webPushBtns[i].addEventListener('click', function (e) {
                        requestPermission();
                    });
                }
            }
        } else if (Notification.permission === 'granted') {
            devMode && console.log('WEB PUSH => permission granted');
            var tryAgainWebPushToken = sessionStorage.getItem('tryAgainWebPushToken');
            if (tryAgainWebPushToken !== 'no') {
                document.addEventListener('click', silentPermission, false);
            }
            /* already have permission, check if token is in database or not */
        }
    }
})();
