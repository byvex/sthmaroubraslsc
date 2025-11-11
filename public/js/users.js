document.addEventListener('alpine:init', function () {
    var routeUsersIndex = (
        typeof ROUTE_USERS_INDEX != 'undefined'
    ) ? ROUTE_USERS_INDEX : '';

    var userEditId = (typeof USER_EDIT_ID != 'undefined') ? USER_EDIT_ID : '';



    var childrenAbortController = null;
    var parentsAbortController = null;

    Alpine.data('userEditData', function () {
        return {
            isSaving: false,
            saveStatus: '',

            children: [],
            childrenErrorMsg: '',
            childrenKeyword: '',
            isLoadingChildren: false,
            isChildrenDropdownOpen: false,
            selectedChildren: [],
            isLoadingSelectedChildren: false,

            parents: [],
            parentsErrorMsg: '',
            parentsKeyword: '',
            isLoadingParents: false,
            isParentsDropdownOpen: false,
            selectedParents: [],
            isLoadingSelectedParents: false,

            isDeletingUser: false,

            handleChildrenKeywordFocus() {
                if (this.children.length) {
                    this.isChildrenDropdownOpen = true;
                }
            },
            handleParentsKeywordFocus() {
                if (this.parents.length) {
                    this.isParentsDropdownOpen = true;
                }
            },
            handleChildrenKeywordChange() {
                this.loadChildren();
            },
            handleParentsKeywordChange() {
                this.loadParents();
            },
            handleFormEnter() {},
            handleRemoveSelectedChild(child) {
                var self = this;
                // iterate backward to avoid index shifting
                for (let i = self.selectedChildren.length - 1; i >= 0; i--) {
                    if (self.selectedChildren[i]['id'] === child.id) {
                        self.selectedChildren.splice(i, 1);
                        break;
                    }
                }
            },
            handleRemoveSelectedParent(parent) {
                var self = this;
                // iterate backward to avoid index shifting
                for (let i = self.selectedParents.length - 1; i >= 0; i--) {
                    if (self.selectedParents[i]['id'] === parent.id) {
                        self.selectedParents.splice(i, 1);
                        break;
                    }
                }
            },
            handleChildrenDropdownClick(child) {
                var self = this;
                self.isChildrenDropdownOpen = false;
                let isDuplicate = false;
                for (var i = 0; i < self.selectedChildren.length; i++) {
                    if (self.selectedChildren[i].id == child.id) {
                        isDuplicate = true;
                        break;
                    }
                }
                if (!isDuplicate) {
                    self.selectedChildren.push({
                        id: child.id,
                        name: child.name,
                        lastname: child.lastname,
                        company: child.company,
                        email: child.email,
                    });
                } else {
                    self.handleRemoveSelectedChild(child);
                }
            },
            handleParentsDropdownClick(parent) {
                var self = this;
                self.isParentsDropdownOpen = false;
                let isDuplicate = false;
                for (var i = 0; i < self.selectedParents.length; i++) {
                    if (self.selectedParents[i].id == parent.id) {
                        isDuplicate = true;
                        break;
                    }
                }
                if (!isDuplicate) {
                    self.selectedParents.push({
                        id: parent.id,
                        name: parent.name,
                        lastname: parent.lastname,
                        company: parent.company,
                        email: parent.email,
                    });
                } else {
                    self.handleRemoveSelectedParent(parent);
                }
            },
            loadChildren() {
                var self = this;
                if (childrenAbortController) {
                    childrenAbortController.abort();
                }
                childrenAbortController = new AbortController();
                self.isLoadingChildren = true;
                self.childrenErrorMsg = '';
                axios.get(routeUsersIndex, {
                    params: {
                        excludeId: userEditId,
                        keyword: self.childrenKeyword,
                    },
                    signal: childrenAbortController.signal,
                }).then(function (res) {
                    dev && console.log(res.data);
                    self.children = res.data.items;
                }).catch(function (err) {
                    dev && console.log(err);
                    self.childrenErrorMsg = getAxiosError(err);
                }).finally(function () {
                    self.isLoadingChildren = false;
                });
            },
            loadParents() {
                var self = this;
                if (parentsAbortController) {
                    parentsAbortController.abort();
                }
                parentsAbortController = new AbortController();
                self.isLoadingParents = true;
                self.parentsErrorMsg = '';
                axios.get(routeUsersIndex, {
                    params: {
                        excludeId: userEditId,
                        keyword: self.parentsKeyword,
                    },
                    signal: parentsAbortController.signal,
                }).then(function (res) {
                    dev && console.log(res.data);
                    self.parents = res.data.items;
                }).catch(function (err) {
                    dev && console.log(err);
                    self.parentsErrorMsg = getAxiosError(err);
                }).finally(function () {
                    self.isLoadingParents = false;
                });
            },
            loadSelectedChildren() {
                var self = this;
                self.isLoadingSelectedChildren = true;
                axios.get(routeUsersIndex, {
                    params: {
                        parentId: userEditId,
                    }
                }).then(function (res) {
                    self.selectedChildren = res.data.items;
                }).catch(function (err) {
                    console.log(err);
                }).finally(function () {
                    self.isLoadingSelectedChildren = false;
                });
            },
            loadSelectedParents() {
                var self = this;
                self.isLoadingSelectedParents = true;
                axios.get(routeUsersIndex, {
                    params: {
                        childId: userEditId,
                    }
                }).then(function (res) {
                    self.selectedParents = res.data.items;
                }).catch(function (err) {
                    console.log(err);
                }).finally(function () {
                    self.isLoadingSelectedParents = false;
                });
            },
            handleEditForm(form) {
                var self = this;
                self.isSaving = true;
                self.saveStatus = 'Please wait, saving...';
                let url = form.getAttribute('action');
                let formData = new FormData(form);
                axios.post(url, formData).then(function (res) {
                    dev && console.log(res.data);
                    if (res.data?.message) {
                        self.saveStatus = res.data?.message;
                    } else {
                        self.saveStatus = 'No response from server';
                    }
                }).catch(function (err) {
                    self.saveStatus = getAxiosError(err);
                }).finally(function () {
                    self.isSaving = false;
                });
            },
            handleUserDeleteForm(form) {
                var self = this;
                self.isDeletingUser = true;
                let formData = new FormData(form);
                axios.post(form.getAttribute('action'),
                    formData
                ).then(function (res) {
                    if(res.data.redirect) {
                        window.location.href = res.data.redirect;
                    } else {
                        let msg = (res.data?.message) ? res.data.message : 'No response from server';
                        Toastify({
                            text: msg,
                            className: (res.data?.success) ? 'toast-success' : 'toast-error',
                            position: 'center',
                        }).showToast();
                    }
                }).catch(function(err){
                    let msg = getAxiosError(err);
                    Toastify({
                        text: msg,
                        className: 'toast-error',
                        position: 'center',
                    }).showToast();
                }).finally(function(){
                    self.isDeletingUser = false;
                });
            },
            init() {
                // this.loadChildren();
                // this.loadSelectedChildren();
                this.loadParents();
                this.loadSelectedParents();
            },
        }
    });
});