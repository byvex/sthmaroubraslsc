<x-admin-layout templates="1" autosize="1" toastify="1">
    <div x-data="templates" class="max-w-screen-xl mx-auto px-4 mb-8">

        <div class="flex flex-wrap justify-between gap-3 mt-4 mb-3">
            <div class="inline-flex flex-wrap gap-3">
                <x-header.nav-btn :href="route('dashboard')">ACTIVITY</x-header.nav-btn>
                <x-header.nav-btn :href="route('contact-groups.index')">GROUPS</x-header.nav-btn>
                <x-header.nav-btn :href="route('contacts.index')">CONTACTS</x-header.nav-btn>
                <x-header.nav-btn active="1" :href="route('templates.index')">TEMPLATES</x-header.nav-btn>
                <x-header.add-btn @click="handleClearSelectedTemplate()">NEW TEMPLATE</x-header.add-btn>
            </div>
            <div class="">
                <x-profile-switcher />
            </div>
        </div>

        <div class="flex flex-wrap bg-white border border-solid border-gray-100 shadow">
            <div class="w-full md:w-4/12 lg:w-3/12 flex flex-col border-solid border-0 border-r border-gray-300">
                <div class="px-4 py-3 flex-none border border-solid border-0 border-b border-gray-300">
                    <div class="text-lg font-semibold font-title mb-2">Templates</div>
                    <div class="block relative">
                        <input type="text" x-model="searchTemplateKeyword" @input.debounce.750ms="handleTemplateSearchKeyword()" spellcheck="false" placeholder="Search templates" class="py-2 pl-8 w-full font-title text-sm rounded border-gray-400 border-solid focus:border-primary-500 focus:ring-primary-400" />
                        <button type="button" x-show="showTemplateSearchKeywordClearBtn" x-cloak x-transition @click="handleTemplateSearchKeywordClearBtn()" title="Clear" class="absolute end-0 top-0 bottom-0 inline-flex items-center justify-center py-2 border-0 text-gray-500 bg-transparent">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="w-5 h-5" fill="currentColor" viewBox="0 -960 960 960">
                                <path d="M480-424 284-228q-11 11-28 11t-28-11q-11-11-11-28t11-28l196-196-196-196q-11-11-11-28t11-28q11-11 28-11t28 11l196 196 196-196q11-11 28-11t28 11q11 11 11 28t-11 28L536-480l196 196q11 11 11 28t-11 28q-11 11-28 11t-28-11L480-424Z" />
                            </svg>
                        </button>
                        <span class="absolute w-8 left-0 top-0 bottom-0 pointer-events-none inline-flex items-center justify-center">
                            <svg x-show="!isLoadingTemplates" xmlns="http://www.w3.org/2000/svg" width="13" height="13" class="" fill="none"><path fill="#515151" d="M10 5.067C10 2.273 7.757 0 5 0S0 2.273 0 5.067c0 2.793 2.243 5.066 5 5.066s5-2.273 5-5.066Zm-5 3.8c-2.068 0-3.75-1.705-3.75-3.8 0-2.096 1.682-3.8 3.75-3.8s3.75 1.704 3.75 3.8c0 2.095-1.682 3.8-3.75 3.8Zm7.317 3.614a.619.619 0 0 1-.884 0L8.507 9.516c.326-.265.623-.565.884-.896l2.926 2.966a.64.64 0 0 1 0 .895Z"/></svg>
                            <span x-show="isLoadingTemplates" x-cloak x-transition.opacity class="absolute top-0 left-0 bottom-0 right-0 inline-flex items-center justify-center text-gray-400">
                                <x-loader class="w-4 h-4" />
                            </span>
                        </span>
                    </div>
                </div>
                <div class="relative min-h-40 h-full grow">
                    <div class="absolute w-full h-full overflow-y-auto scrollbar-thin">
                        <div x-show="isLoadingTemplates" class="absolute left-0 top-0 bottom-0 backdrop-blur-sm right-0 text-center text-primary-500 py-3">
                            <x-loader class="w-7 h-7" />
                        </div>
                        <div x-show="!isLoadingTemplates && templates.length == 0" x-cloak>
                            <div class="py-2 text-center">No Templates Found!</div>
                        </div>
                        <template x-for="tpl in templates" :key="tpl.id">
                            <button type="button" @click="handleSelectTemplate(tpl)" :class="tpl.id == currentTemplateId ? 'bg-gray-200':'bg-transparent hover:bg-gray-50'" class="px-2 py-0 border-0 flex font-normal text-left w-full">
                                <span class="inline-block truncate px-3 py-2 grow border border-solid border-0 border-b" :class="tpl.id == currentTemplateId ? 'border-gray-200':'border-gray-300'" x-text="tpl.name"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-8/12 lg:w-9/12 relative">
                <form @submit.prevent="handleSaveTemplate($el)" action="{{ route('templates.store') }}">
                    <div class="flex">
                        <div class="w-full lg:w-8/12 px-4 py-4">
                            <div class="text-lg mb-3 font-title font-semibold">
                                <span x-show="!currentTemplateId?.length">New Template</span>
                                <span x-show="currentTemplateId?.length" x-text="currentTemplateName"></span>
                            </div>
                            <div class="flex flex-wrap -mx-2">
                                <div class="w-full md:w-6/12 px-2 mb-3">
                                    <div>Profile</div>
                                    <select name="profile_id" x-model="currentTemplateProfile" class="w-full py-2 rounded border-gray-400 border-solid focus:border-primary-500 focus:ring-primary-400">
                                        @foreach($profiles as $profile_id => $profile)
                                            <option value="{{ $profile_id }}">{{ $profile['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-full md:w-6/12 px-2 mb-3">
                                    <div>Template Name*</div>
                                    <input type="text" name="name" x-model="currentTemplateName" required class="w-full py-2 rounded border-gray-400 border-solid focus:border-primary-500 focus:ring-primary-400" />
                                </div>
                                <div class="w-full px-2 mb-3">
                                    <div>Message</div>
                                    <div>
                                        <div @click.away="isPersonalizeDropdownOpen = false">
                                            <div class="border border-solid border-0 border-t border-l border-r rounded-t border-gray-400">
                                                <button type="button" @click="isPersonalizeDropdownOpen = !isPersonalizeDropdownOpen" class="text-xs border-0 bg-transparent leading-tight focus:outline-none text-gray-500 px-2 py-1">
                                                    <span>Personalise</span>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 -960 960 960"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
                                                </button>
                                            </div>
                                            <div class="relative px-1">
                                                <div x-show="isPersonalizeDropdownOpen" x-cloak x-transition.scale.origin.top.left class="absolute my-1 max-w-56 rounded border border-solid border-gray-200 text-sm bg-white shadow-lg">
                                                    <button type="button" class="w-full px-2 py-1 border-b border-gray-200 border-solid text-left text-gray-500 border-0 bg-transparent" @click="handlePersonalizeItemClick('Firstname')">First Name [Firstname]</button>
                                                    <button type="button" class="w-full px-2 py-1 border-b border-gray-200 border-solid text-left text-gray-500 border-0 bg-transparent" @click="handlePersonalizeItemClick('Lastname')">Last Name [Lastname]</button>
                                                    <button type="button" class="w-full px-2 py-1 border-b border-gray-200 border-solid text-left text-gray-500 border-0 bg-transparent" @click="handlePersonalizeItemClick('Mobile')">Phone [Mobile]</button>
                                                    <button type="button" class="w-full px-2 py-1 border-gray-200 border-solid text-left text-gray-500 border-0 bg-transparent" @click="handlePersonalizeItemClick('Company')">Company [Company]</button>
                                                </div>
                                            </div>
                                        </div>
                                        <textarea name="message" rows="6" id="current-template-message-input" @input.debouce.500ms="handleMsgInput()" x-model="currentTemplateMsg" class="w-full py-2 rounded-b border-gray-400 border-solid focus:border-primary-500 focus:ring-primary-400"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="w-full lg:w-4/12 px-4 py-4">
                            <div x-cloak class="w-64 relative mx-auto my-3">
                                <img src="/img/ui/iphone-frame.png" alt="iphone" class="w-full h-auto select-none pointer-events-none" />
                                <div id="iphone-sms-box" class="absolute text-sm text-white">
                                    <div id="iphone-sms-content" class="relative scrollbar-thin">
                                        <textarea id="iphone-sms-textarea" class="border-0 bg-transparent w-full h-full px-0 py-0 focus:ring-0 text-white resize-none" x-model="currentTemplateMsg"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end items-center px-4 pb-6">
                        <div class="flex gap-1 items-center">
                            <div x-show="currentTemplateId" x-cloak :class="isOpenDeleteTemplateForm ? 'z-50 border-gray-400':'' " class="relative inline-flex items-center gap-2 px-4 py-3 border border-solid border-white rounded bg-white">
                                <div x-cloak x-show="isOpenDeleteTemplateForm" class="max-w-64 leading-tight text-sm text-gray-500">
                                    Are you sure you want to delete this template? This action cannot be undone
                                </div>
                                <button x-cloak @click="handleCloseDeleteContactGroupForm()" x-show="isOpenDeleteTemplateForm" type="button" title="Close" class="absolute -top-2 -right-2 w-6 h-6 px-0 py-0 border text-gray-500 border-solid border-gray-400 inline-flex items-center justify-center rounded-full bg-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="w-5 h-5" fill="currentColor" viewBox="0 -960 960 960">
                                        <path d="M480-424 284-228q-11 11-28 11t-28-11q-11-11-11-28t11-28l196-196-196-196q-11-11-11-28t11-28q11-11 28-11t28 11l196 196 196-196q11-11 28-11t28 11q11 11 11 28t-11 28L536-480l196 196q11 11 11 28t-11 28q-11 11-28 11t-28-11L480-424Z" />
                                    </svg>
                                </button>
                                <button @click="handleDeleteTemplate()" :disabled="isDeletingTemplate" type="button" :class="isOpenDeleteTemplateForm ? 'border-primary-500 bg-primary-500':'border-gray-400 bg-gray-400' " class="inline-flex gap-2 text-sm font-semibold rounded px-4 py-2 justify-center items-center leading-none no-underline border border-solid text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="w-5 h-5" fill="currentColor" viewBox="0 -960 960 960"><path d="M280-120q-33 0-56.5-23.5T200-200v-520q-17 0-28.5-11.5T160-760q0-17 11.5-28.5T200-800h160q0-17 11.5-28.5T400-840h160q17 0 28.5 11.5T600-800h160q17 0 28.5 11.5T800-760q0 17-11.5 28.5T760-720v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM400-280q17 0 28.5-11.5T440-320v-280q0-17-11.5-28.5T400-640q-17 0-28.5 11.5T360-600v280q0 17 11.5 28.5T400-280Zm160 0q17 0 28.5-11.5T600-320v-280q0-17-11.5-28.5T560-640q-17 0-28.5 11.5T520-600v280q0 17 11.5 28.5T560-280ZM280-720v520-520Z"/></svg>
                                    <span x-text="isDeletingTemplate ? 'DELETING...':'DELETE'">DELETE</span>
                                </button>
                            </div>
                            <div class="py-3 border border-solid border-white">
                                <button type="submit" x-show="isCreateFormOpen" :disabled="isSavingTemplate" class="inline-flex gap-2 font-title font-semibold rounded px-4 py-2 text-sm justify-center items-center leading-none no-underline border border-solid border-primary-500 text-white bg-primary-500 disabled:opacity-50">
                                    <svg x-show="!currentTemplateId" class="w-4 h-5" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"/></svg>
                                    <svg x-show="currentTemplateId" xmlns="http://www.w3.org/2000/svg" x-cloak width="24" height="24" class="w-5 h-5" fill="currentColor" viewBox="0 -960 960 960"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h261q20 0 30 12.5t10 27.5q0 15-10.5 27.5T460-760H200v560h560v-261q0-20 12.5-30t27.5-10q15 0 27.5 10t12.5 30v261q0 33-23.5 56.5T760-120H200Zm280-360Zm-120 80v-97q0-16 6-30.5t17-25.5l344-344q12-12 27-18t30-6q16 0 30.5 6t26.5 18l56 57q11 12 17 26.5t6 29.5q0 15-5.5 29.5T897-728L553-384q-11 11-25.5 17.5T497-360h-97q-17 0-28.5-11.5T360-400Zm481-384-56-56 56 56ZM440-440h56l232-232-28-28-29-28-231 231v57Zm260-260-29-28 29 28 28 28-28-28Z"/></svg>
                                    <span x-show="!currentTemplateId" x-text="isSavingTemplate ? 'SAVING...':'CREATE TEMPLATE'">CREATE TEMPLATE</span>
                                    <span x-show="currentTemplateId" x-cloak x-text="isSavingTemplate ? 'SAVING...':'SAVE'">SAVE</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <div :class="[showEditExitForm ? 'z-50' : '']" class="absolute top-0 right-0 max-w-72">
                    <div x-cloak x-show="showEditExitForm" x-transition.opacity class="px-3 py-2 rounded shadow-lg border border-solid border-gray-400 bg-white">
                        <div class="mb-3 text-sm text-gray-500">Are you sure you want to exit? Your template will not be saved, this cannot be undone.</div>
                        <div class="flex gap-2 mb-1">
                            <button @click="showEditExitForm = false" type="button" class="inline-flex gap-2 font-semibold rounded px-3 py-2 text-sm justify-center items-center leading-none border border-solid border-primary-500 text-white bg-primary-500 disabled:opacity-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-3" fill="currentColor" viewBox="0 0 512 512"><path d="M463.5 224H472c13.3 0 24-10.7 24-24V72c0-9.7-5.8-18.5-14.8-22.2s-19.3-1.7-26.2 5.2L413.4 96.6c-87.6-86.5-228.7-86.2-315.8 1c-87.5 87.5-87.5 229.3 0 316.8s229.3 87.5 316.8 0c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0c-62.5 62.5-163.8 62.5-226.3 0s-62.5-163.8 0-226.3c62.2-62.2 162.7-62.5 225.3-1L327 183c-6.9 6.9-8.9 17.2-5.2 26.2s12.5 14.8 22.2 14.8H463.5z"/></svg>
                                <span>GO BACK</span>
                            </button>
                            <button @click="handleClearSelectedTemplate(true)" type="button" class="inline-flex gap-2 text-sm font-semibold rounded px-4 py-2 justify-center items-center leading-none border border-solid text-white border-gray-400 bg-gray-400">
                                <span>YES, EXIT</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 512 512"><path d="M352 96l64 0c17.7 0 32 14.3 32 32l0 256c0 17.7-14.3 32-32 32l-64 0c-17.7 0-32 14.3-32 32s14.3 32 32 32l64 0c53 0 96-43 96-96l0-256c0-53-43-96-96-96l-64 0c-17.7 0-32 14.3-32 32s14.3 32 32 32zm-9.4 182.6c12.5-12.5 12.5-32.8 0-45.3l-128-128c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L242.7 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l210.7 0-73.4 73.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l128-128z"/></svg>
                            </button>
                        </div>
                    </div>
                    <button @click="handleClearSelectedTemplate()" x-show="currentTemplateId" x-cloak type="button" title="Close" class="absolute -top-2 -right-2 w-7 h-7 px-0 py-0 border text-gray-500 border-solid border-gray-400 inline-flex items-center justify-center rounded-full bg-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="w-5 h-5" fill="currentColor" viewBox="0 -960 960 960"><path d="M480-424 284-228q-11 11-28 11t-28-11q-11-11-11-28t11-28l196-196-196-196q-11-11-11-28t11-28q11-11 28-11t28 11l196 196 196-196q11-11 28-11t28 11q11 11 11 28t-11 28L536-480l196 196q11 11 11 28t-11 28q-11 11-28 11t-28-11L480-424Z"/></svg>
                    </button>
                </div>
            </div>
        </div>


        <div class="modal fade" id="templateCreatedModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="templateCreatedModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content !bg-primary-500">
                    <div class="modal-body text-center">
                        <img src="https://dummyimage.com/50" alt="template" class="my-2 mb-3 rounded" />
                        <p id="templateCreatedModalLabel" class="mb-2 text-white">Hooray! You created a new template</p>
                        <button data-bs-dismiss="modal" type="button" class="border-0 bg-transparent font-semibold text-white inline-flex gap-1 items-center justify-center">
                            <span>VIEW TEMPLATE</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/>
                            </svg>
                        </button>
                        <button type="button" title="Close" @click="handleClearSelectedTemplate()" aria-label="Close" class="absolute -top-2 -right-1 w-7 h-7 px-0 py-0 border text-gray-500 border-solid border-gray-400 inline-flex items-center justify-center rounded-full bg-white">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="w-5 h-5" fill="currentColor" viewBox="0 -960 960 960">
                                <path d="M480-424 284-228q-11 11-28 11t-28-11q-11-11-11-28t11-28l196-196-196-196q-11-11-11-28t11-28q11-11 28-11t28 11l196 196 196-196q11-11 28-11t28 11q11 11 11 28t-11 28L536-480l196 196q11 11 11 28t-11 28q-11 11-28 11t-28-11L480-424Z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <span x-show="isOpenDeleteTemplateForm || showEditExitForm" x-cloak x-transition.opacity class="fixed top-0 right-0 bottom-0 left-0 bg-black/50"></span>
    </div>
    <x-slot name="headScript">
        <script type="text/javascript">
            var TEMPLATES_ROUTE_INDEX = "{{ route('templates.index') }}";
            var TEMPLATES_ROUTE_DELETE = "{{ route('templates.delete') }}";
        </script>
    </x-slot>
</x-admin-layout>
