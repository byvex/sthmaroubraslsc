document.addEventListener('alpine:init', function () {
    var iphoneSmsTextarea = document.getElementById('iphone-sms-textarea');
    if (iphoneSmsTextarea && typeof autosize == 'function') {
        autosize(iphoneSmsTextarea);
    }

    var newSmsModal = new bsModal('#newSmsModal');
    var smsCreatedModal = new bsModal('#smsCreatedModal');

    var currentTemplateMessageInput = document.getElementById('current-template-message-input');
    var templatesAbortController = null;
    var contactGroupAbortController = null;

    var templatesRouteIndex = TEMPLATES_ROUTE_INDEX;
    var contactGroupsRouteIndex = CONTACT_GROUPS_ROUTE_INDEX;
    var smsRouteIndex = SMS_ROUTE_INDEX;

    var self = {};
    Alpine.data('activityData', function () {
        return {
            isLoadingContactGroups: false,

            // written "sms" as "items" for better naming
            items: [],
            page: 1,
            keyword: '',
            keywordRecipient: '',
            totalItems: 0,
            totalPages: 1,
            isLoadingItems: false,
            canAutoLoadItems: true,
            filterStatus: '',
            filterStartDate: '',
            filterEndDate: '',
            filterFolder: '',

            isPersonalizeDropdownOpen: false,
            currentTemplateMsg: '',
            showSmsEditExitForm: false,

            templates: [],
            selectedTemplateId: '',
            isLoadingTemplates: false,
            searchTemplateKeyword: '',
            showTemplateSearchKeywordClearBtn: false,

            send_at: '',
            scheduled: true,
            resMessage: '',
            resMessage2: '',
            sending: 'contact', // sending to contact or adhoc number

            contactGroupKeyword: '',
            contactGroups: [],
            selectedContactGroups: [],
            isContactGroupDropdownOpen: false,
            isLoadingContactGroups: false,

            contactGroupContacts: [],
            selectedContactGroupContacts: [],

            separateNumbers: [],

            isFormEdited: false,
            isSavingSms: false,

            handleNewSmsForm(form) {
                let formData = new FormData(form);
                let url = form.getAttribute('action');
                self.isSavingSms = true;
                self.resMessage = '';
                self.resMessage2 = '';
                axios.post(url, formData).then(function (res) {
                    dev && console.log(res.data);
                    self.handleExitSmsForm();
                    self.scheduled = res.data?.scheduled;
                    self.resMessage = (res.data?.message) ? res.data.message : 'No response from server';
                    self.resMessage2 = (res.data?.message2) ? res.data.message2 : '';
                    smsCreatedModal.show();
                    setTimeout(function(){
                        self.items = [];
                        self.canAutoLoadItems = true;
                        self.loadItems(1);
                    }, 2000);
                }).catch(function (err) {
                    dev && console.log(err);
                    let msg = getAxiosError(err);
                    Toastify({
                        text: msg,
                        className: 'toast-error',
                        position: 'center',
                    }).showToast();
                }).finally(function () {
                    self.isSavingSms = false;
                });
            },
            handleCloseModalBtn() {
                if (self.isFormEdited) {
                    self.showSmsEditExitForm = true;
                } else {
                    self.isFormEdited = false;
                    self.showSmsEditExitForm = false;
                    self.separateNumbers = [];
                    self.selectedContactGroups = [];
                    newSmsModal.hide();
                    self.$refs?.newSmsFormRef?.reset();
                }
            },
            loadTemplates() {
                if (templatesAbortController) {
                    templatesAbortController.abort();
                }
                templatesAbortController = new AbortController();
                self.isLoadingTemplates = true;
                axios.get(templatesRouteIndex, {
                    params: { keyword: self.searchTemplateKeyword },
                    signal: templatesAbortController.signal,
                }).then(function (res) {
                    dev && console.log(res.data);
                    if (res.data?.success) {
                        self.templates = res.data.items;
                    } else {
                        let msg = (res.data?.message) ? res.data.message : 'No response from server';
                        Toastify({
                            text: msg,
                            className: (res.data?.success) ? 'toast-success' : 'toast-error',
                            position: 'center',
                        }).showToast();
                    }
                    self.isLoadingTemplates = false;
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
                        self.isLoadingTemplates = false;
                    }
                });
            },
            handleMsgInput() {
                autosize.update(iphoneSmsTextarea);
            },
            handlePersonalizeItemClick(word) {
                word = '[' + word + ']';
                self.isPersonalizeDropdownOpen = false;
                var startPos = currentTemplateMessageInput.selectionStart;
                var endPos = currentTemplateMessageInput.selectionEnd;
                var text = self.currentTemplateMsg;
                self.currentTemplateMsg = text.substring(0, startPos) + word + text.substring(endPos, text.length);
                // Move the cursor to the end of the inserted word
                self.$nextTick(() => {
                    currentTemplateMessageInput.selectionStart = currentTemplateMessageInput.selectionEnd = startPos + word.length;
                    // Set focus back to the textarea
                    currentTemplateMessageInput.focus();
                });
            },
            handleContactGroupKeywordChange() {
                if (contactGroupAbortController) {
                    contactGroupAbortController.abort();
                }
                self.isLoadingContactGroups = true;
                self.isContactGroupDropdownOpen = true;
                contactGroupAbortController = new AbortController();
                axios.get(contactGroupsRouteIndex, {
                    signal: contactGroupAbortController.signal,
                    params: {
                        need_contacts: 1,
                        keyword: self.contactGroupKeyword,
                    }
                }).then(function (res) {
                    dev && console.log(res.data);
                    if (res.data?.success) {
                        self.contactGroups = res.data.items;
                        self.contactGroupContacts = res.data.contacts;
                        self.handleDuplicateContactGroupMarking();
                        self.handleDuplicateContactGroupContactMarking();
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
                if (this.contactGroups.length || this.contactGroupContacts.length) {
                    this.isContactGroupDropdownOpen = true;
                }
            },
            handleDuplicateContactGroupMarking() {
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
            handleDuplicateContactGroupContactMarking() {
                self.$nextTick(() => {
                    for (var j = 0; j < self.contactGroupContacts.length; j++) {
                        self.contactGroupContacts[j].added = false;
                        console.log(self.contactGroupContacts);
                        if (self.selectedContactGroupContacts?.length) {
                            for (var i = 0; i < self.selectedContactGroupContacts.length; i++) {
                                if (self.contactGroupContacts[j].id == self.selectedContactGroupContacts[i].id) {
                                    self.contactGroupContacts[j].added = true;
                                    break;
                                }
                            }
                        }
                    }
                });
            },
            handleRemoveSelectedContactGroup(group) {
                // iterate backward to avoid index shifting
                for (let i = self.selectedContactGroups.length - 1; i >= 0; i--) {
                    if (self.selectedContactGroups[i]['id'] === group.id) {
                        self.selectedContactGroups.splice(i, 1);
                        break;
                    }
                }
                self.handleDuplicateContactGroupMarking();
            },
            handleRemoveSelectedContactGroupContact(contact) {
                // iterate backward to avoid index shifting
                for (let i = self.selectedContactGroupContacts.length - 1; i >= 0; i--) {
                    if (self.selectedContactGroupContacts[i]['id'] === contact.id) {
                        self.selectedContactGroupContacts.splice(i, 1);
                        break;
                    }
                }
                self.handleDuplicateContactGroupMarking();
            },
            handleContactGroupContactDropdownClick(group) {
                self.isContactGroupDropdownOpen = false;
                let isDuplicate = false;
                for (var i = 0; i < self.selectedContactGroupContacts.length; i++) {
                    if (self.selectedContactGroupContacts[i].id == group.id) {
                        isDuplicate = true;
                        break;
                    }
                }
                if (!isDuplicate) {
                    self.selectedContactGroupContacts.push({
                        id: group.id,
                        name: group.name,
                    });
                } else {
                    self.handleRemoveSelectedContactGroupContact(group);
                }
                self.handleDuplicateContactGroupContactMarking();
            },
            handleContactGroupDropdownClick(group) {
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
            handleFormEnter() {},
            handleContactGroupClickEnter(e = null) {
                // e.preventDefault();
                // e.stopPropagation();
                self.isContactGroupDropdownOpen = false;
                self.isLoadingContactGroups = false;
                if (self.contactGroupKeyword.trim().length) {
                    if (self.separateNumbers.indexOf(self.contactGroupKeyword) == -1) {
                        self.separateNumbers.push(self.contactGroupKeyword.trim());
                        if (contactGroupAbortController) {
                            contactGroupAbortController.abort();
                        }
                    }
                }
                self.contactGroupKeyword = '';
            },
            handleRemoveSeparateNumber(separateNumber) {
                // iterate backward to avoid index shifting
                for (let i = self.separateNumbers.length - 1; i >= 0; i--) {
                    if (self.separateNumbers[i] === separateNumber) {
                        self.separateNumbers.splice(i, 1);
                        break;
                    }
                }
            },
            sendSmsFormChanged() {
                this.isFormEdited = true;
            },
            handleTemplateSelected() {
                if (self.selectedTemplateId) {
                    for (var i = 0; i < self.templates.length; i++) {
                        if (this.selectedTemplateId == self.templates[i].id) {
                            self.currentTemplateMsg = self.templates[i].message;
                            autosize.update(iphoneSmsTextarea);
                            break;
                        }
                    }
                }
            },
            handleExitSmsForm() {
                self.isFormEdited = false;
                newSmsModal.hide();
                self.separateNumbers = [];
                self.selectedContactGroups = [];
                self.selectedContactGroupContacts = [];
                self.showSmsEditExitForm = false;
                self.$refs?.newSmsFormRef?.reset();
            },
            handleFilterChange() {
                self.items = [];
                self.canAutoLoadItems = true;
                self.loadItems(1);
            },
            handleKeywordChange() {
                self.handleFilterChange();
            },
            handleFilterStatusChange() {
                self.handleFilterChange();
            },
            loadItems(page) {
                if (self.isLoadingItems) return;
                if (!self.canAutoLoadItems) return;
                page = page ? page : self.page;
                self.isLoadingItems = true;
                axios.get(smsRouteIndex, {
                    params: {
                        page: page,
                        keyword: self.keyword,
                        filterStartDate: self.filterStartDate,
                        filterEndDate: self.filterEndDate,
                        filterStatus: self.filterStatus,
                        keywordRecipient: self.keywordRecipient,
                        filterFolder: self.filterFolder,
                    }
                }).then(function (res) {
                    dev && console.log(res.data);
                    self.items.push(...res.data.items);
                    self.page = res.data.page;
                    self.totalPages = res.data.totalPages;
                    self.totalItems = res.data.totalRows;
                    if (res.data.items.length == 0) {
                        self.canAutoLoadItems = false;
                        setTimeout(function () {
                            self.canAutoLoadItems = true;
                        }, 5000);
                        self.isLoadingItems = false;
                    } else {
                        self.isLoadingItems = false;
                        self.canAutoLoadItems = true;
                    }
                    dev && console.log(self.items);
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
                        self.isLoadingItems = false;
                    }
                    self.isLoadingItems = false;
                });
            },
            init() {
                self = this;
                self.loadTemplates();
                self.loadItems(1);

                var sendAtEl = document.getElementById('send_at');
                if (sendAtEl) {
                    flatpickr('#send_at', {
                        disableMobile: 'true',
                        allowInput: true,
                        enableTime: true,
                        altInput: true,
                        altFormat: 'd/m/Y h:i K',
                        dateFormat: 'Y-m-d H:i:s',
                        onChange: function (selectedDates, dateStr, instance) {
                            self.send_at = dateStr;
                        },
                        onClose: function (selectedDates, dateStr, instance) {
                            self.send_at = dateStr;
                        }
                    });
                }

                var filterStartDateEl = document.getElementById('filterStartDateEl');
                if (filterStartDateEl) {
                    flatpickr('#filterStartDateEl', {
                        disableMobile: 'true',
                        allowInput: true,
                        enableTime: true,
                        altInput: true,
                        altFormat: 'd/m/Y h:i K',
                        dateFormat: 'Y-m-d H:i:s',
                        onChange: function (selectedDates, dateStr, instance) {
                            self.filterStartDate = dateStr;
                        },
                        onClose: function (selectedDates, dateStr, instance) {
                            self.filterStartDate = dateStr;
                            self.handleFilterChange();
                        }
                    });
                }

                var filterEndDateEl = document.getElementById('filterEndDateEl');
                if (filterEndDateEl) {
                    flatpickr('#filterEndDateEl', {
                        disableMobile: 'true',
                        allowInput: true,
                        enableTime: true,
                        altInput: true,
                        altFormat: 'd/m/Y h:i K',
                        dateFormat: 'Y-m-d H:i:s',
                        onChange: function (selectedDates, dateStr, instance) {
                            self.filterEndDate = dateStr;
                        },
                        onClose: function (selectedDates, dateStr, instance) {
                            self.filterEndDate = dateStr;
                            self.handleFilterChange();
                        }
                    });
                }


                setInterval(function () {
                    // self.items = [];
                    // self.loadItems(1)
                    if(document.body.classList.contains('modal-open')) {

                    } else {
                        window.location.reload();
                    }
                    // Toastify({
                    //     text: 'Refreshing activity...',
                    //     className: 'toast-success',
                    //     position: 'center',
                    // }).showToast();
                }, 30000);


                window.addEventListener('scroll', function (e) {
                    const { clientHeight, scrollTop, scrollHeight } = e.target.documentElement;
                    if ((clientHeight + scrollTop + 50) >= scrollHeight && !self.isLoadingItems) {
                        let page = self.page + 1;
                        self.loadItems(page)
                        // dev && console.log('loading...');
                    }
                });
            },
        }
    });
});
