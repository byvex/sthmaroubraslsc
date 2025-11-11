document.addEventListener('alpine:init', function(){
    var iphoneSmsTextarea = document.getElementById('iphone-sms-textarea');
    if(iphoneSmsTextarea && typeof autosize == 'function'){
        autosize(iphoneSmsTextarea);
    }

    var templateCreatedModal = new bsModal('#templateCreatedModal');

    var currentTemplateMessageInput = document.getElementById('current-template-message-input');
    var templatesAbortController = null;

    templatesRouteIndex = TEMPLATES_ROUTE_INDEX;
    templatesRouteDelete = TEMPLATES_ROUTE_DELETE;

    Alpine.data('templates', function(){
        return {
            mounted: true,
            isPersonalizeDropdownOpen: false,
            currentTemplateId: '',
            currentTemplateName: '',
            currentTemplateProfile: '',
            currentTemplateMsg: '',

            page: 1,
            templates: [],
            isCreateFormOpen: true,
            isSavingTemplate: false,
            isLoadingTemplates: false,
            isEditing: false,
            showEditExitForm: false,

            isDeletingTemplate: false,
            isOpenDeleteTemplateForm: false,
            searchTemplateKeyword: '',
            showTemplateSearchKeywordClearBtn: false,

            handleTemplateSearchKeyword(){
                if(this.searchTemplateKeyword.trim().length){
                    this.showTemplateSearchKeywordClearBtn = true;
                } else {
                    this.showTemplateSearchKeywordClearBtn = false;
                }
                this.loadTemplates(1);
            },
            handleTemplateSearchKeywordClearBtn(){
                this.searchTemplateKeyword = '';
                this.loadTemplates(1);
                this.showTemplateSearchKeywordClearBtn = false;
            },
            loadTemplates(page){
                var self = this;
                if(templatesAbortController){
                    templatesAbortController.abort();
                }
                templatesAbortController = new AbortController();
                self.isLoadingTemplates = true;
                page = page ? page : self.page;
                axios.get(templatesRouteIndex, {
                    params: {keyword: self.searchTemplateKeyword},
                    signal: templatesAbortController.signal,
                }).then(function(res){
                    dev && console.log(res.data);
                    if(res.data?.success){
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
                }).catch(function(err){
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

            handleSelectTemplate(template){
                this.currentTemplateId = template.id;
                this.currentTemplateName = template.name;
                this.currentTemplateMsg = template.message;
                this.currentTemplateProfile = template.profile_id;
                this.isEditing = true;
            },

            handlePersonalizeItemClick(word){
                var self = this;
                word = '[' + word + ']';
                self.isPersonalizeDropdownOpen = false;
                var startPos = currentTemplateMessageInput.selectionStart;
                var endPos = currentTemplateMessageInput.selectionEnd;
                var text = self.currentTemplateMsg;
                self.currentTemplateMsg = text.substring(0, startPos) + word + text.substring(endPos, text.length);
                // Move the cursor to the end of the inserted word
                self.$nextTick(()=> {
                    currentTemplateMessageInput.selectionStart = currentTemplateMessageInput.selectionEnd = startPos + word.length;
                    // Set focus back to the textarea
                    currentTemplateMessageInput.focus();
                });
            },
            handleMsgInput(){
                autosize.update(iphoneSmsTextarea)
            },
            handleDeleteTemplate(){
                var self = this;
                if(self.isOpenDeleteTemplateForm) {
                    self.isDeletingTemplate = true;
                    axios.post(templatesRouteDelete, {
                        id: self.currentTemplateId,
                    }).then(function(res){
                        dev && console.log(res.data);
                        if(res.data.success){
                            self.loadTemplates(1);
                            self.isEditing = false;
                            self.showEditExitForm = false;
                            self.isOpenDeleteTemplateForm = false;
                            self.handleClearSelectedTemplate();
                        }
                        let msg = (res.data?.message) ? res.data.message : 'No response from server';
                        Toastify({
                            text: msg,
                            className: (res.data?.success) ? 'toast-success' : 'toast-error',
                            position: 'center',
                        }).showToast();
                    }).catch(function(err){
                        dev && console.error(err);
                        let msg = getAxiosError(err);
                        Toastify({
                            text: msg,
                            className: 'toast-error',
                            position: 'center',
                        }).showToast();
                        self.isLoadingContactGroups = false;
                    }).finally(function(){
                        self.isDeletingTemplate = false;
                    });
                } else {
                    self.isOpenDeleteTemplateForm = true;
                }
            },
            handleCloseDeleteContactGroupForm(){
                this.isOpenDeleteTemplateForm = false;
            },
            handleSaveTemplate(form){
                var self = this;
                var data = new FormData(form);
                data.append('id', self.currentTemplateId);
                var url = form.getAttribute('action');
                self.isSavingTemplate = true;
                axios.post(url, data).then(function(res){
                    dev && console.log(res.data);
                    let msg = (res.data?.message) ? res.data.message : 'No response from server';
                    if(res.data?.reset){
                        form.reset();
                    }
                    if(res.data?.reload){
                        self.loadTemplates(1);
                    }
                    if(res.data?.id){
                        self.currentTemplateId = res.data.id;
                    }
                    if(res.data?.success){
                        if(res.data?.updating){
                            Toastify({
                                text: msg,
                                className: (res.data?.success) ? 'toast-success' : 'toast-error',
                                position: 'center',
                            }).showToast();
                        } else {
                            templateCreatedModal.show();
                        }
                    } else {
                        Toastify({
                            text: msg,
                            className: (res.data?.success) ? 'toast-success' : 'toast-error',
                            position: 'center',
                        }).showToast();
                    }
                }).catch(function(err){
                    dev && console.error(err);
                    let msg = getAxiosError(err);
                    Toastify({
                        text: msg,
                        className: 'toast-error',
                        position: 'center',
                    }).showToast();
                }).finally(function(){
                    self.isSavingTemplate = false;
                });
            },
            handleClearSelectedTemplate(forced = false){
                if(!forced && this.isEditing){
                    if(!this.showEditExitForm) {
                        this.showEditExitForm = true;
                    } else {
                        this.showEditExitForm = false;
                    }
                    return;
                }
                this.isEditing = false;
                this.showEditExitForm = false;
                this.currentTemplateId = '';
                this.currentTemplateName = '';
                this.currentTemplateMsg = '';
                this.currentTemplateProfile = '';
                templateCreatedModal.hide();
            },
            init(){
                this.loadTemplates(1);
            }
        }
    });
});