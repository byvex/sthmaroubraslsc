var profileSwither = document.getElementById('profile-switcher');
if(profileSwither && typeof USERS_PROFILE_UPDATE_INDEX != 'undefined') {
    profileSwither.addEventListener('change', function(e){
        axios.post(USERS_PROFILE_UPDATE_INDEX, {
            id: profileSwither.value
        }).then(function(res){
            window.location.reload();
        }).catch(function(err){
            msg = getAxiosError(err);
            Toastify({
                text: msg,
                className: 'toast-error',
                position: 'center',
            }).showToast();
        });
    });
}


var appForms = document.querySelectorAll('[data-js="app-form"]');
if (appForms) {
    for (var i = 0; i < appForms.length; i++) {
        appForms[i].addEventListener('submit', function (e) {
            e.preventDefault();
            let form = this;
            let data = new FormData(form);
            if (typeof tinyMCE == 'object') {
                var myContentEl = tinyMCE?.get('my-tinymce-editor');
                if (myContentEl) {
                    data.append('content', myContentEl.getContent());
                }
            }
            if (typeof appCkEditors == 'object') {
                for (var i = 0; i < appCkEditors.length; i++) {
                    data.append(appCkEditors[i].name, appCkEditors[i].editor.getData());
                }
            }

            let url = form.getAttribute('action');
            let submitBtn = form.querySelector('[data-js="app-form-btn"]');
            let submitStatus = form.querySelector('[data-js="app-form-status"]');
            // let submitBtnLoader = submitBtn.querySelector('[data-js="btn-loader"]');
            submitBtn.disabled = true;
            // submitBtnLoader.classList.remove('hidden');
            if (submitStatus) {
                submitStatus.textContent = 'Please wait...';
                submitStatus.classList.remove('hidden');
            }
            axios.post(url, data).then(function (res) {
                if (res.data?.redirect) {
                    window.location.href = res.data.redirect;
                }
                let msg = (res.data?.message) ? res.data.message : 'No response from server';
                if (submitStatus) {
                    submitStatus.textContent = msg;
                }
                if (res.data?.remove == true) {
                    submitBtn.remove();
                }
                if (res.data?.reset) {
                    form.reset();
                }
                Toastify({
                    text: msg,
                    className: (res.data?.success) ? 'toast-success' : 'toast-error',
                    position: 'center',
                }).showToast();
                if (res.data?.success) {
                    var previewImgInputs = document.querySelectorAll('[data-js="preview-img-input"]');
                    if (previewImgInputs) {
                        for (var k = 0; k < previewImgInputs.length; k++) {
                            previewImgInputs[k].value = '';
                        }
                    }
                }
                if (res.data?.console) {
                    console.log(res.data);
                } else {
                    dev && console.log('appForms: ', res.data);
                }
            }).catch(function (err) {
                let msg = getAxiosError(err);
                Toastify({
                    text: msg,
                    className: 'toast-error',
                    position: 'center',
                }).showToast();
                if (submitStatus) {
                    submitStatus.textContent = msg;
                }
                dev && console.log(err);
            }).finally(function () {
                submitBtn.disabled = false;
                // submitBtnLoader.classList.add('hidden');
            });
        });
    }
}
