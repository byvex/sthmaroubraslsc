document.addEventListener('alpine:init', function () {
    var newContactsModalEl = document.getElementById('newContactsModal');
    const newContactsModal = new bsModal(newContactsModalEl);
    var contactGroupsRouteIndex = CONTACT_GROUPS_ROUTE_INDEX;
    var contactsRouteIndex = CONTACTS_ROUTE_INDEX;
    var contactsRouteDelete = CONTACTS_ROUTE_DELETE;

    const importContactsModal = new bsModal('#importContactGroupsModal');
    var importContactsFileEl = document.getElementById('import_contacts_file');

    var contactGroupAbortController = null;
    var contactAbortController = null;

    if (newContactsModalEl) {
        newContactsModalEl.addEventListener('shown.bs.modal', function () {
            document.getElementById('new-contact-modal-first-input')?.focus();
        });
    }

    Alpine.data('contacts', function () {
        return {
            mounted: false,
            isSavingContact: false,

            searchKeywordName: '',
            showSearchKeywordNameClearBtn: false,
            searchKeywordPhone: '',
            showSearchKeywordPhoneClearBtn: false,

            modalInputId: '',
            modalInputName: '',
            modalInputLastname: '',
            modalInputPhone: '',
            modalInputEmail: '',
            modalInputCountry: '',
            modalInputCompany: '',
            modalInputDob: '',
            modalInputMemberId: '',
            modalInputComments: '',
            modalInputMemberCategoryName: '',

            orderDirection: 'asc',
            orderColumn: 'name',

            page: 1,
            perPage: 10,
            contacts: [],
            prevPages: [],
            nextPages: [],
            totalPages: 0,
            totalContacts: 0,
            isLoadingContacts: false,
            canAutoLoadItems: true,

            importContactsFilename: '',
            importContactsDisabled: true,
            isImportingContacts: false,
            importContactStep: 'upload',
            importContactsErrorMsg: '',
            countNewPhoneNumbers: 0,

            currentDeleteContact: null,
            isDeletingContact: false,
            isEditingContact: false,

            contactGroupKeyword: '',
            contactGroups: [],
            selectedContactGroups: [],
            isContactGroupDropdownOpen: false,
            isLoadingContactGroups: false,

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
                this.canAutoLoadItems = true;
                this.contacts = [];
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

            handleCloseModalBtn() {
                newContactsModal.hide();
                var self = this;
                self.isEditingContact = false;
                if (self.modalInputId?.length) {
                    self.loadContacts();
                }
                self.modalInputId = '';
                self.modalInputName = '';
                self.modalInputLastname = '';
                self.modalInputPhone = '';
                self.modalInputEmail = '';
                self.modalInputCountry = '';
                self.modalInputCompany = '';
                self.modalInputDob = '';
                self.modalInputComments = '';
                self.modalInputMemberId = '';
                self.selectedContactGroups = [];
                self.modalInputMemberCategoryName = '';
                self.handleDuplicateContactGroupMarking();
            },

            handleEditContactBtn(contact) {
                newContactsModal.show();
                var self = this;
                self.isEditingContact = true;
                self.modalInputId = contact.id;
                self.modalInputName = contact.name;
                self.modalInputLastname = contact.lastname;
                self.modalInputPhone = contact.phone;
                if (contact.dob?.length) {
                    self.modalInputDob = contact.dob.split('T')[0];
                } else {
                    self.modalInputDob = '';
                }
                self.modalInputEmail = contact.email ?? '';
                self.modalInputCountry = contact.country;
                self.modalInputCompany = contact.company;
                self.modalInputComments = contact.comments;
                self.modalInputMemberId = contact.member_uid ?? '';
                self.modalInputMemberCategoryName = contact.member_category_name ?? '';
                if (contact?.groups?.length) {
                    self.selectedContactGroups = contact.groups;
                } else {
                    self.selectedContactGroups = [];
                }
                self.handleDuplicateContactGroupMarking();
            },

            handleSearchKeywordName() {
                if (this.searchKeywordName.trim().length) {
                    this.showSearchKeywordNameClearBtn = true;
                } else {
                    this.showSearchKeywordNameClearBtn = false;
                }
                this.canAutoLoadItems = true;
                this.contacts = [];
                this.loadContacts(1);
            },
            handleSearchKeywordPhone() {
                if (this.searchKeywordPhone.trim().length) {
                    this.showSearchKeywordPhoneClearBtn = true;
                } else {
                    this.showSearchKeywordPhoneClearBtn = false;
                }
                this.canAutoLoadItems = true;
                this.contacts = [];
                this.loadContacts(1);
            },
            handleClearSearchKeywordName() {
                this.searchKeywordName = '';
                this.showSearchKeywordNameClearBtn = false;
                this.canAutoLoadItems = true;
                this.contacts = [];
                this.loadContacts(1);
            },
            handleClearSearchKeywordPhone() {
                this.searchKeywordPhone = '';
                this.showSearchKeywordPhoneClearBtn = false;
                this.canAutoLoadItems = true;
                this.contacts = [];
                this.loadContacts(1);
            },

            handleDeleteContact(contact) {
                this.currentDeleteContact = contact;
            },
            removeContactById(id){
                var self = this;
                for (var i = self.contacts.length - 1; i >= 0; i--) {
                    if(self.contacts[i].id == id) {
                        self.contacts.splice(i, 1);
                        break;
                    }
                }

            },
            handleConfirmedDeleteContact(contact) {
                var self = this;
                self.isDeletingContact = true;
                axios.post(contactsRouteDelete, {
                    id: contact.id
                }).then(function (res) {
                    if (res.data.success) {
                        self.canAutoLoadItems = true;
                        self.removeContactById(contact.id);
                        self.currentDeleteContact = null;
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
                }).finally(function () {
                    self.isDeletingContact = false;
                });
            },
            handleCancelDeleteContact() {
                this.currentDeleteContact = null;
            },

            handleNewContactForm(form) {
                var self = this;
                self.isSavingContact = true;
                var data = new FormData(form);
                var url = form.getAttribute('action');
                axios.post(url, data).then(function (res) {
                    dev && console.log(res.data);
                    if (res.data?.reset) {
                        form.reset();
                    }
                    if (res.data?.close) {
                        newContactsModal.hide();
                        self.selectedContactGroups = [];
                        self.handleDuplicateContactGroupMarking();
                    }
                    if (res.data?.reload) {
                        self.contacts = [];
                        self.loadContacts(1);
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
                    self.isSavingContact = false;
                });
            },
            handleContactGroupKeywordChange() {
                var self = this;
                if (contactGroupAbortController) {
                    contactGroupAbortController.abort();
                }
                self.isLoadingContactGroups = true;
                self.isContactGroupDropdownOpen = true;
                contactGroupAbortController = new AbortController();
                axios.get(contactGroupsRouteIndex + '?keyword=' + self.contactGroupKeyword, {
                    signal: contactGroupAbortController.signal,
                }).then(function (res) {
                    dev && console.log(res.data);
                    if (res.data?.success) {
                        self.contactGroups = res.data.items;
                        self.handleDuplicateContactGroupMarking();
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
                        self.isContactGroupDropdownOpen = false;
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
            handleContactGroupKeywordFocus() {
                if (this.contactGroups.length) {
                    this.isContactGroupDropdownOpen = true;
                }
            },
            handleDuplicateContactGroupMarking() {
                var self = this;
                self.$nextTick(() => {
                    for (var j = 0; j < self.contactGroups.length; j++) {
                        self.contactGroups[j].added = false;
                        if (self.selectedContactGroups?.length) {
                            for (var i = 0; i < self.selectedContactGroups.length; i++) {
                                if (self.contactGroups[j].id == self.selectedContactGroups[i].id) {
                                    self.contactGroups[j].added = true;
                                    break;
                                }
                            }
                        }
                    }
                });
            },
            handleRemoveSelectedContactGroup(group) {
                var self = this;
                // iterate backward to avoid index shifting
                for (let i = self.selectedContactGroups.length - 1; i >= 0; i--) {
                    if (self.selectedContactGroups[i]['id'] === group.id) {
                        self.selectedContactGroups.splice(i, 1);
                        break;
                    }
                }
                self.handleDuplicateContactGroupMarking();
            },
            handleContactGroupDropdownClick(group) {
                var self = this;
                self.isContactGroupDropdownOpen = false;
                let isDuplicate = false;
                for (var i = 0; i < self.selectedContactGroups.length; i++) {
                    if (self.selectedContactGroups[i].id == group.id) {
                        isDuplicate = true;
                        break;
                    }
                }
                if (!isDuplicate) {
                    self.selectedContactGroups.push({
                        id: group.id,
                        uid: group.uid,
                        name: group.name,
                    });
                } else {
                    self.handleRemoveSelectedContactGroup(group);
                }
                self.handleDuplicateContactGroupMarking();
            },
            loadContacts(page) {
                var self = this;
                if (contactAbortController) {
                    contactAbortController.abort();
                }
                contactAbortController = new AbortController();
                if (self.isLoadingContacts) return;
                if (!self.canAutoLoadItems) return;
                self.isLoadingContacts = true;
                page = page ? page : self.contactPage;

                axios.get(contactsRouteIndex, {
                    params: {
                        page: page,
                        phone: self.searchKeywordPhone,
                        orderColumn: self.orderColumn,
                        orderDirection: self.orderDirection,
                        keyword: self.searchKeywordName,
                    },
                    signal: contactAbortController.signal,
                }).then(function (res) {
                    dev && console.log(res.data);
                    if (res.data?.success) {
                        dev && console.log(res.data.items);
                        self.contacts.push(...res.data.items);
                        self.page = res.data.page;
                        self.totalContacts = res.data.totalRows;
                        self.totalPages = res.data.totalPages;
                        self.prevPages = [];
                        self.nextPages = [];
                        for (let i = self.page - 2; i < self.page; i++) {
                            if (i > 0) self.prevPages.push(i);
                        }
                        for (let j = self.page + 1; j < self.totalPages; j++) {
                            self.nextPages.push(j);
                            if (j >= self.page + 2) break;
                        }
                        if (res.data.items.length == 0) {
                            self.canAutoLoadItems = false;
                            setTimeout(function () {
                                self.canAutoLoadItems = true;
                            }, 5000);
                            self.isLoadingContacts = false;
                        } else {
                            self.isLoadingContacts = false;
                            self.canAutoLoadItems = true;
                        }
                    } else {
                        let msg = (res.data?.message) ? res.data.message : 'No response from server';
                        Toastify({
                            text: msg,
                            className: (res.data?.success) ? 'toast-success' : 'toast-error',
                            position: 'center',
                        }).showToast();
                    }
                    self.isLoadingContacts = false;
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
                        self.isLoadingContacts = false;
                    }
                });
            },
            init() {
                this.loadContacts(1);
                this.mounted = true;
                var self = this;
                window.addEventListener('scroll', function (e) {
                    const { clientHeight, scrollTop, scrollHeight } = e.target.documentElement;
                    if ((clientHeight + scrollTop + 50) >= scrollHeight && !self.isLoadingContacts) {
                        let page = self.page + 1;
                        self.loadContacts(page)
                        dev && console.log('loading...');
                    }
                });
            }
        }
    });
});