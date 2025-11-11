document.addEventListener('alpine:init', function () {
    const importContactsModal = new bsModal('#importContactGroupsModal');
    var contactGroupsRouteIndex = CONTACT_GROUPS_ROUTE_INDEX;
    var contactGroupsRouteDelete = CONTACT_GROUPS_ROUTE_DELETE;
    var contactRouteIndexRoute = CONTACT_ROUTE_INDEX;
    var currentContactGroupAbortController = null;
    var importContactsFileEl = document.getElementById('import_contacts_file');

    var contactOverflowEl = document.getElementById('contact-overflow-el');
    var contactGroupsOverflowEl = document.getElementById('contact-groups-overflow-el');

    Alpine.data('contactGroups', function () {
        return {
            mounted: false,
            isLoadingContacts: false,
            isLoadingContactGroups: false,
            isCreatingContactGroup: false,
            isOpenNewContactGroupForm: false,
            isOpenEditContactGroupForm: false,
            isDeletingContactGroup: false,
            isOpenDeleteContactGroupForm: false,

            orderDirection: 'asc',
            orderColumn: 'name',

            currentContactGroup: { id: 0, name: '', createdBy: '', createdOn: '', profile: '' },

            importContactsFilename: '',
            importContactsDisabled: true,
            isImportingContacts: false,
            importContactStep: 'upload',
            importContactsErrorMsg: '',
            countNewPhoneNumbers: 0,

            showContactGroupSearchClearBtn: false,
            contactGroupSearchKeyword: '',
            contactGroups: [],
            contactGroupPage: 1,
            totalContactGroupPages: 1,
            totalContactGroupRows: 0,
            isFirstTimeContactGroupsLoaded: true,
            canAutoloadContactGroups: true,

            contactPage: 1,
            contactPerPage: 10,
            contactTotalRows: 0,
            contactTotalPages: 1,
            contacts: [],
            contactSearchKeyword: '',
            canAutoloadContacts: true,


            flipOrderDirection() {
                if(this.orderDirection == 'asc') {
                    this.orderDirection = 'desc';
                } else {
                    this.orderDirection = 'asc';
                }
            },
            handleOrderClick(column = 'name') {
                if(this.orderColumn == column) {
                    this.flipOrderDirection();
                } else {
                    this.orderColumn = column;
                }
                this.contacts = [];
                this.canAutoloadContacts = true;
                this.loadContacts(1);
            },
            handleImportContactsFile(e) {
                var self = this;
                if (e.target.files?.length) {
                    let file = e.target.files[0];
                    self.importContactsFilename = file.name;
                    self.importContactsDisabled = false;
                } else {
                    self.importContactsDisabled = true;
                    self.importContactsFilename = '';
                }
            },

            handleImportContactsForm(form) {
                var self = this;
                self.isImportingContacts = true;
                let formData = new FormData(form);
                formData.append('group_id', self.currentContactGroup.id);
                formData.append('step', self.importContactStep);
                let url = form.getAttribute('action');
                axios.post(url, formData).then(function (res) {
                    if (res.data.hasNewPhoneNumbers) {
                        self.importContactStep = 'hasNewPhoneNumbers';
                        self.countNewPhoneNumbers = res.data.newPhoneNumbers;
                    } else {
                        self.importContactsErrorMsg = res.data.message;
                        self.importContactStep = 'complete';
                    }
                }).catch(function (err) {
                    dev && console.error(err);
                    let msg = getAxiosError(err);
                    self.importContactsErrorMsg = msg;
                    Toastify({
                        text: msg,
                        className: 'toast-error',
                        position: 'center',
                    }).showToast();
                }).finally(function () {
                    self.isImportingContacts = false;
                });
            },

            closeImportContactsModal() {
                importContactsModal.hide();
                importContactsFileEl.value = '';
                this.importContactsFilename = '';
                this.importContactsErrorMsg = '';
                this.countNewPhoneNumbers = 0;
                this.importContactsDisabled = true;
                this.importContactStep = 'upload';
            },

            clearSelectedContactGroup() {
                this.currentContactGroup = { id: 0, name: '', createdBy: '', createdOn: '', profile: '' }
            },
            handleDeleteContactGroup() {
                var self = this;
                self.isOpenEditContactGroupForm = false;
                self.$refs.newGroupNameInputRef.value = '';
                if (this.isOpenDeleteContactGroupForm) {
                    self.isDeletingContactGroup = true;
                    axios.post(contactGroupsRouteDelete, {
                        id: self.currentContactGroup.id,
                    }).then(function (res) {
                        dev && console.log(res.data);
                        if (res.data.success) {
                            self.loadContactGroups();
                            self.clearSelectedContactGroup();
                            self.isOpenDeleteContactGroupForm = false;
                        }
                        let msg = (res.data?.message) ? res.data.message : 'No response from server';
                        Toastify({
                            text: msg,
                            className: (res.data?.success) ? 'toast-success' : 'toast-error',
                            position: 'center',
                        }).showToast();
                    }).catch(function (err) {
                        dev && console.error(err);
                        let msg = getAxiosError(err);
                        Toastify({
                            text: msg,
                            className: 'toast-error',
                            position: 'center',
                        }).showToast();
                        self.isLoadingContactGroups = false;
                    }).finally(function () {
                        self.isDeletingContactGroup = false;
                    });
                } else {
                    self.isOpenDeleteContactGroupForm = true;
                }
            },
            handleCloseDeleteContactGroupForm() {
                this.isOpenDeleteContactGroupForm = false;
            },
            handleContactGroupInput() {
                if (this.contactGroupSearchKeyword.trim().length) {
                    this.showContactGroupSearchClearBtn = true;
                } else {
                    this.showContactGroupSearchClearBtn = false;
                }
                this.contactGroups = [];
                this.canAutoloadContactGroups = true;
                this.loadContactGroups(1);
            },
            handleClearSearchContactGroup() {
                this.contactGroupSearchKeyword = '';
                this.showContactGroupSearchClearBtn = false;
                this.canAutoloadContactGroups = true;
                this.contactGroups = [];
                this.loadContactGroups(1);
            },
            handleSelectGroup(contactGroup) {
                var self = this;
                self.isOpenNewContactGroupForm = false;
                self.currentContactGroup = {
                    id: contactGroup.id,
                    name: contactGroup.name,
                    createdBy: contactGroup.createdBy,
                    createdOn: contactGroup.createdOn,
                    profile: contactGroup.profile,
                }
                self.contacts = [];
                self.canAutoloadContacts = true;
                self.loadContacts(1);
            },
            handleOpenNewContactGroupForm() {
                var self = this;
                this.isOpenNewContactGroupForm = true;
                this.clearSelectedContactGroup();
                this.$nextTick(function () {
                    self.$refs.newGroupNameInputRef.focus();
                });
            },
            handleOpenEditContactGroupForm() {
                var self = this;
                this.isOpenEditContactGroupForm = true;
                this.$nextTick(function () {
                    self.$refs.newGroupNameInputRef.value = self.currentContactGroup.name;
                    self.$refs.newGroupNameInputRef.focus();
                });
            },
            handleCancelGroup() {
                if (this.isOpenEditContactGroupForm) {
                    this.$refs.newGroupNameInputRef.value = '';
                    this.isOpenEditContactGroupForm = false
                } else {
                    this.clearSelectedContactGroup();
                }
                this.isOpenNewContactGroupForm = false;
            },
            handleContactsSearch(){
                this.contacts = [];
                this.canAutoloadContacts = true;
                this.loadContacts(1);
            },
            loadContacts(page) {
                var self = this;
                if (self.isLoadingContacts) return;
                if (!self.canAutoloadContacts) return;
                page = page ? page : self.contactPage;
                self.isLoadingContacts = true;
                axios.get(contactRouteIndexRoute, {
                    params: {
                        page: page,
                        contactGroupId: self.currentContactGroup.id,
                        keyword: self.contactSearchKeyword,
                        orderColumn: self.orderColumn,
                        orderDirection: self.orderDirection,
                    }
                }).then(function (res) {
                    dev && console.log(res.data);
                    self.contacts.push(...res.data.items);
                    self.contactPage = res.data.page;
                    self.contactTotalPages = res.data.totalPages;
                    self.contactTotalRows = res.data.totalRows;
                    if (res.data.items.length == 0) {
                        self.canAutoloadContacts = false;
                        setTimeout(function () {
                            self.canAutoloadContacts = true;
                        }, 4000);
                    } else {
                        self.canAutoloadContacts = true;
                    }
                }).catch(function (err) {
                    dev && console.error(err);
                    let msg = getAxiosError(err);
                    Toastify({
                        text: msg,
                        className: 'toast-error',
                        position: 'center',
                    }).showToast();
                }).finally(function () {
                    self.isLoadingContacts = false;
                });
            },
            loadContactGroups(page) {
                var self = this;
                if (self.isLoadingContactGroups) return;
                if (!self.canAutoloadContactGroups) return;
                page = page ? page : self.contactGroupPage;
                // If there's an ongoing request, abort it
                if (currentContactGroupAbortController) {
                    currentContactGroupAbortController.abort();
                }
                self.isLoadingContactGroups = true;
                // Create a new AbortController
                currentContactGroupAbortController = new AbortController();
                axios.get(contactGroupsRouteIndex, {
                    params: {
                        page: page,
                        keyword: self.contactGroupSearchKeyword,
                    },
                    signal: currentContactGroupAbortController.signal,
                }).then(function (res) {
                    dev && console.log(res.data);
                    if (res.data?.success) {
                        self.contactGroups.push(...res.data.items);
                        self.contactGroupPage = res.data.page;
                        self.totalContactGroupPages = res.data.totalPages;
                        self.totalContactGroupRows = res.data.totalRows;
                        if (res.data.items.length == 0) {
                            self.canAutoloadContactGroups = false;
                            setTimeout(function () {
                                self.canAutoloadContactGroups = true;
                            }, 4000);
                        } else {
                            self.canAutoloadContactGroups = true;
                        }
                        if (self.isFirstTimeContactGroupsLoaded && res.data.items.length) {
                            self.currentContactGroup = {
                                id: res.data.items[0].id,
                                name: res.data.items[0].name,
                                createdBy: res.data.items[0].createdBy,
                                createdOn: res.data.items[0].createdOn,
                                profile: res.data.items[0].profile,
                            }
                            self.loadContacts(1);
                            self.isFirstTimeContactGroupsLoaded = false;
                        }
                    } else {
                        let msg = (res.data?.message) ? res.data.message : 'No response from server';
                        Toastify({
                            text: msg,
                            className: (res.data?.success) ? 'toast-success' : 'toast-error',
                            position: 'center',
                        }).showToast();
                    }
                    self.isLoadingContactGroups = false;
                }).catch(function (err) {
                    if (err.code === 'ERR_CANCELED') {
                    } else {
                        dev && console.error(err);
                        let msg = getAxiosError(err);
                        Toastify({
                            text: msg,
                            className: 'toast-error',
                            position: 'center',
                        }).showToast();
                        self.isLoadingContactGroups = false;
                    }
                });
            },
            handleCreateContactGroup(form) {
                var self = this;
                self.isCreatingContactGroup = true;
                var url = form.getAttribute('action');
                var data = new FormData(form);
                if (self.isOpenEditContactGroupForm && self.currentContactGroup?.id) {
                    data.append('id', self.currentContactGroup.id);
                }
                axios.post(url, data).then(function (res) {
                    if (res.data?.success) {
                        self.isOpenNewContactGroupForm = false;
                        self.isOpenEditContactGroupForm = false;
                        // newGroupModal.hide();
                        self.currentContactGroup = {
                            id: res.data.item.id,
                            name: res.data.item.name,
                            createdBy: res.data.item.createdBy,
                            createdOn: res.data.item.createdOn,
                            profile: res.data.item.profile,
                        }
                        self.loadContacts(1);
                        self.contactGroups = [];
                        self.canAutoloadContactGroups = true;
                        self.loadContactGroups(1);
                        self.contacts = [];
                        self.canAutoloadContacts = true;
                        self.loadContacts(1);
                    }
                    if (res.data?.reset) {
                        form.reset();
                    }
                    dev && console.log(res.data);
                    let msg = (res.data?.message) ? res.data.message : 'No response from server';
                    Toastify({
                        text: msg,
                        className: (res.data?.success) ? 'toast-success' : 'toast-error',
                        position: 'center',
                    }).showToast();
                }).catch(function (err) {
                    dev && console.error(err);
                    let msg = getAxiosError(err);
                    Toastify({
                        text: msg,
                        className: 'toast-error',
                        position: 'center',
                    }).showToast();
                }).finally(function () {
                    self.isCreatingContactGroup = false;
                });
            },
            init() {
                var self = this;
                self.mounted = true;
                self.loadContactGroups(1);

                contactOverflowEl.addEventListener('scroll', function (_) {
                    let { clientHeight, scrollTop, scrollHeight } = contactOverflowEl;
                    if (
                        (clientHeight + scrollTop + 50) >= scrollHeight &&
                        !self.isLoadingContacts
                    ) {
                        let page = self.contactPage + 1;
                        self.loadContacts(page);
                        dev && console.log('loading...');
                    }
                });

                contactGroupsOverflowEl.addEventListener('scroll', function (_) {
                    let { clientHeight, scrollTop, scrollHeight } = contactGroupsOverflowEl;
                    if (
                        (clientHeight + scrollTop + 50) >= scrollHeight &&
                        !self.isLoadingContactGroups
                    ) {
                        let page = self.contactGroupPage + 1;
                        self.loadContactGroups(page);
                        dev && console.log('loading...');
                    }
                });
            },
        }
    });
});