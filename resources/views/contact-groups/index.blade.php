<x-admin-layout contactGroups="1" toastify="1" flags="1">
    <div x-data="contactGroups" class="max-w-screen-xl mx-auto px-4 mb-8">

        <div class="flex flex-wrap justify-between gap-3 mt-4 mb-3">
            <div class="inline-flex flex-wrap gap-3">
                <x-header.nav-btn :href="route('dashboard')">ACTIVITY</x-header.nav-btn>
                <x-header.nav-btn active="1" :href="route('contact-groups.index')">GROUPS</x-header.nav-btn>
                <x-header.nav-btn :href="route('contacts.index')">CONTACTS</x-header.nav-btn>
                <x-header.nav-btn :href="route('templates.index')">TEMPLATES</x-header.nav-btn>
                <x-header.add-btn x-show="mounted" @click="handleOpenNewContactGroupForm()" style="display:none">NEW GROUP</x-header.add-btn>
            </div>
            <div class="">
                <x-profile-switcher />
            </div>
        </div>

        <div :class="(isOpenNewContactGroupForm || isOpenEditContactGroupForm) ? 'relative z-50':''" class="flex flex-wrap bg-white rounded border border-solid border-gray-100 shadow">
            <div class="w-full md:w-4/12 lg:w-3/12 flex flex-col border-solid border-0 border-r border-gray-300">
                <div class="px-4 py-3 flex-none border border-solid border-0 border-b border-gray-300">
                    <div class="text-lg font-semibold font-title mb-2">Groups <span x-show="contactGroups.length" x-text="'(' + contactGroups.length + ')'"></span></div>
                    <div class="block relative">
                        <input type="text" x-model="contactGroupSearchKeyword" @input.debounce.750ms="handleContactGroupInput()" placeholder="Search groups" class="py-2 pl-8 w-full font-title text-sm rounded border-gray-400 border-solid focus:border-primary-500 focus:ring-primary-400" />
                        <button type="button" x-show="showContactGroupSearchClearBtn" x-cloak x-transition @click="handleClearSearchContactGroup()" title="Clear" class="absolute end-0 top-0 bottom-0 inline-flex items-center justify-center py-2 border-0 text-gray-500 bg-transparent">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="w-5 h-5" fill="currentColor" viewBox="0 -960 960 960">
                                <path d="M480-424 284-228q-11 11-28 11t-28-11q-11-11-11-28t11-28l196-196-196-196q-11-11-11-28t11-28q11-11 28-11t28 11l196 196 196-196q11-11 28-11t28 11q11 11 11 28t-11 28L536-480l196 196q11 11 11 28t-11 28q-11 11-28 11t-28-11L480-424Z" />
                            </svg>
                        </button>
                        <span class="absolute left-0 top-0 bottom-0 pointer-events-none px-3 inline-flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" class="" fill="none">
                                <path fill="#515151" d="M10 5.067C10 2.273 7.757 0 5 0S0 2.273 0 5.067c0 2.793 2.243 5.066 5 5.066s5-2.273 5-5.066Zm-5 3.8c-2.068 0-3.75-1.705-3.75-3.8 0-2.096 1.682-3.8 3.75-3.8s3.75 1.704 3.75 3.8c0 2.095-1.682 3.8-3.75 3.8Zm7.317 3.614a.619.619 0 0 1-.884 0L8.507 9.516c.326-.265.623-.565.884-.896l2.926 2.966a.64.64 0 0 1 0 .895Z" />
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="relative min-h-40 h-full grow">
                    <div id="contact-groups-overflow-el" class="absolute w-full h-full overflow-y-auto scrollbar-thin">
                        <div x-show="!isLoadingContactGroups && contactGroups.length == 0" x-cloak>
                            <div class="py-2 text-center">No Goups Found!</div>
                        </div>
                        <template x-for="contactGroup in contactGroups" :key="contactGroup.id">
                            <button type="button" :title="'ID: '+ contactGroup.id" @click="handleSelectGroup(contactGroup)" :class="contactGroup.id == currentContactGroup.id ? 'bg-gray-200':'bg-transparent hover:bg-gray-50'" class="px-2 py-0 border-0 flex font-normal text-left w-full">
                                <span class="inline-block truncate px-3 py-2 grow border border-solid border-0 border-b" :class="contactGroup.id == currentContactGroup.id ? 'border-gray-200':'border-gray-300'" x-text="contactGroup.name + ' (' + contactGroup.total + ')'"></span>
                            </button>
                        </template>
                        {{-- <div class="text-center text-primary-500 py-3">
                            <p x-show="!isLoadingContactGroups" x-cloak class="text-sm">Page <span x-text="contactGroupPage"></span> of <span x-text="totalContactGroupPages"></span>, showing <span x-text="contactGroups.length"></span> of <span x-text="totalContactGroupRows"></span></p>
                            <div x-show="isLoadingContactGroups">
                                <x-loader class="w-7 h-7" />
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>
            <div class="w-full md:w-8/12 lg:w-9/12 ">
                <div class="flex flex-wrap gap-3 relative">
                    <form @submit.prevent="handleCreateContactGroup($el)" action="{{ route('contact-groups.store') }}" class="px-4 py-4 grow flex flex-col justify-between">
                        <div class="mb-3">
                            <div x-cloak x-show="isOpenNewContactGroupForm || isOpenEditContactGroupForm">
                                <div class="text-lg font-title font-semibold mb-2">New Group</div>
                                <div class="text-gray-500">Group Name*</div>
                                <input type="text" name="name" x-ref="newGroupNameInputRef" required placeholder="Enter name" class="py-2 w-full max-w-sm font-title text-sm rounded border-gray-400 border-solid focus:border-primary-500 focus:ring-primary-400" />
                            </div>
                            <div x-show="!isOpenNewContactGroupForm && !isOpenEditContactGroupForm" class="text-lg font-title font-semibold">
                                <span x-cloak x-show="currentContactGroup?.id" x-text="currentContactGroup.name"></span>
                                <span x-show="currentContactGroup?.id == 0">Select a group</span>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <div class="inline-block relative">
                                <input type="text" x-model="contactSearchKeyword" @input.debounce="handleContactsSearch()" placeholder="Search for contact in group" class="py-2 pl-8 w-60 max-w-full font-title text-sm rounded border-gray-400 border-solid focus:border-primary-500 focus:ring-primary-400" />
                                <span class="absolute left-0 top-0 bottom-0 pointer-events-none px-3 inline-flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" class="" fill="none">
                                        <path fill="#515151" d="M10 5.067C10 2.273 7.757 0 5 0S0 2.273 0 5.067c0 2.793 2.243 5.066 5 5.066s5-2.273 5-5.066Zm-5 3.8c-2.068 0-3.75-1.705-3.75-3.8 0-2.096 1.682-3.8 3.75-3.8s3.75 1.704 3.75 3.8c0 2.095-1.682 3.8-3.75 3.8Zm7.317 3.614a.619.619 0 0 1-.884 0L8.507 9.516c.326-.265.623-.565.884-.896l2.926 2.966a.64.64 0 0 1 0 .895Z" />
                                    </svg>
                                </span>
                            </div>
                            <button type="submit" x-cloak x-show="isOpenNewContactGroupForm || isOpenEditContactGroupForm" class="inline-flex gap-2 font-title text-sm font-semibold rounded px-5 py-2 justify-center items-center leading-none no-underline border border-solid border-gray-600 text-white bg-gray-700" :disabled="isCreatingContactGroup">
                                <span x-show="isOpenNewContactGroupForm" x-text="isCreatingContactGroup ? 'ADDING...' : 'ADD'"></span>
                                <span x-show="isOpenEditContactGroupForm" x-text="isCreatingContactGroup ? 'UPDATING...' : 'UPDATE'"></span>
                            </button>
                        </div>
                    </form>
                    <div class="px-4 py-4 self-end text-sm text-gray-400 font-normal flex-none text-right">
                        <div x-show="currentContactGroup?.id" x-cloak x-text="currentContactGroup.createdBy"></div>
                        <div x-show="currentContactGroup?.id" x-cloak x-text="currentContactGroup.createdOn"></div>
                        <div x-show="currentContactGroup?.id" x-cloak x-text="currentContactGroup.profile"></div>
                        <div class="flex gap-2 justify-end items-center mt-3">
                            <button x-show="currentContactGroup?.id" x-cloak type="button" data-bs-toggle="modal" data-bs-target="#importContactGroupsModal" class="inline-flex gap-2 font-title text-sm font-semibold rounded px-4 py-2 justify-center items-center leading-none no-underline border border-solid border-primary-500 text-white bg-primary-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" width="24" height="24" fill="currentColor" viewBox="0 -960 960 960"><path d="M440-367v127q0 17 11.5 28.5T480-200q17 0 28.5-11.5T520-240v-127l36 36q6 6 13.5 9t15 2.5q7.5-.5 14.5-3.5t13-9q11-12 11.5-28T612-388L508-492q-6-6-13-8.5t-15-2.5q-8 0-15 2.5t-13 8.5L348-388q-12 12-11.5 28t12.5 28q12 11 28 11.5t28-11.5l35-35ZM240-80q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h287q16 0 30.5 6t25.5 17l194 194q11 11 17 25.5t6 30.5v447q0 33-23.5 56.5T720-80H240Zm280-560v-160H240v640h480v-440H560q-17 0-28.5-11.5T520-640ZM240-800v200-200 640-640Z"/></svg>
                                <span>IMPORT GROUP LIST</span>
                            </button>
                            <a x-show="currentContactGroup?.id" :href="'{{ route('contacts.export.download') }}?id=' + currentContactGroup?.id" title="Export" target="_blank" rel="noopener noreferrer nofollow" class="inline-flex gap-2 font-title text-sm font-semibold rounded px-2 py-2 justify-center items-center leading-none no-underline border border-solid border-gray-600 text-white bg-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" width="24" height="24" fill="currentColor" viewBox="0 -960 960 960"><path d="M480-337q-8 0-15-2.5t-13-8.5L308-492q-12-12-11.5-28t11.5-28q12-12 28.5-12.5T365-549l75 75v-286q0-17 11.5-28.5T480-800q17 0 28.5 11.5T520-760v286l75-75q12-12 28.5-11.5T652-548q11 12 11.5 28T652-492L508-348q-6 6-13 8.5t-15 2.5ZM240-160q-33 0-56.5-23.5T160-240v-80q0-17 11.5-28.5T200-360q17 0 28.5 11.5T240-320v80h480v-80q0-17 11.5-28.5T760-360q17 0 28.5 11.5T800-320v80q0 33-23.5 56.5T720-160H240Z"/></svg>
                            </a>
                        </div>
                    </div>
                    <button type="button" x-show="isOpenNewContactGroupForm || isOpenEditContactGroupForm || currentContactGroup?.id" x-cloak x-transition @click="handleCancelGroup()" title="Close" class="absolute -top-2 -right-2 w-7 h-7 px-0 py-0 border text-gray-500 border-solid border-gray-400 inline-flex items-center justify-center rounded-full bg-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="w-5 h-5" fill="currentColor" viewBox="0 -960 960 960">
                            <path d="M480-424 284-228q-11 11-28 11t-28-11q-11-11-11-28t11-28l196-196-196-196q-11-11-11-28t11-28q11-11 28-11t28 11l196 196 196-196q11-11 28-11t28 11q11 11 11 28t-11 28L536-480l196 196q11 11 11 28t-11 28q-11 11-28 11t-28-11L480-424Z" />
                        </svg>
                    </button>
                </div>
                <div id="contact-overflow-el" class="overflow-auto scrollbar-thin h-96">
                    <table class="w-full" style="table-layout:fixed;">
                        <thead>
                            <tr class="select-none top-0 z-1" style="position:sticky;">
                                <th @click.prevent="handleOrderClick('name')" class="px-4 py-2 font-semibold text-white bg-black cursor-pointer">
                                    <span>Name</span>
                                    <svg x-show="orderColumn == 'name' && orderDirection == 'asc'" x-cloak xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi" viewBox="0 0 16 16">
                                        <path d="m7.247 4.86-4.796 5.481c-.566.647-.106 1.659.753 1.659h9.592a1 1 0 0 0 .753-1.659l-4.796-5.48a1 1 0 0 0-1.506 0z"/>
                                    </svg>
                                    <svg x-show="orderColumn == 'name' && orderDirection == 'desc'" x-cloak xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi" viewBox="0 0 16 16">
                                        <path d="M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z"/>
                                    </svg>
                                </th>
                                <th @click.prevent="handleOrderClick('phone')" class="px-4 py-2 font-semibold text-white bg-black cursor-pointer">
                                    <span>Mobile Number</span>
                                    <svg x-show="orderColumn == 'phone' && orderDirection == 'asc'" x-cloak xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi" viewBox="0 0 16 16">
                                        <path d="m7.247 4.86-4.796 5.481c-.566.647-.106 1.659.753 1.659h9.592a1 1 0 0 0 .753-1.659l-4.796-5.48a1 1 0 0 0-1.506 0z"/>
                                    </svg>
                                    <svg x-show="orderColumn == 'phone' && orderDirection == 'desc'" x-cloak xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi" viewBox="0 0 16 16">
                                        <path d="M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z"/>
                                    </svg>
                                </th>
                                <th @click.prevent="handleOrderClick('company')" class="px-4 py-2 font-semibold text-white bg-black cursor-pointer">
                                    <span>Company</span>
                                    <svg x-show="orderColumn == 'company' && orderDirection == 'asc'" x-cloak xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi" viewBox="0 0 16 16">
                                        <path d="m7.247 4.86-4.796 5.481c-.566.647-.106 1.659.753 1.659h9.592a1 1 0 0 0 .753-1.659l-4.796-5.48a1 1 0 0 0-1.506 0z"/>
                                    </svg>
                                    <svg x-show="orderColumn == 'company' && orderDirection == 'desc'" x-cloak xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi" viewBox="0 0 16 16">
                                        <path d="M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z"/>
                                    </svg>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="">
                            <template x-for="contact in contacts" :key="contact.id">
                                <tr class="bg-white relative">
                                    <td class="px-4 py-2 border-0 border-b border-solid border-gray-300 text-sm">
                                        <span x-text="contact.name"></span>
                                        <span x-text="contact.lastname"></span>
                                    </td>
                                    <td class="px-4 py-2 border-0 border-b border-solid border-gray-300 text-sm">
                                        <span :class="'fi-' + contact?.country?.toLowerCase()" class="country-flag fi mr-1"></span>
                                        <span x-text="contact.phone"></span>
                                    </td>
                                    <td class="px-4 py-2 border-0 border-b border-solid border-gray-300 text-sm">
                                        <span x-text="contact.company"></span>
                                    </td>
                                </tr>
                            </template>
                            <tr>
                                <td></td>
                                <td class="px-4 py-2 text-sm text-center">
                                    <span x-show="!isLoadingContacts" x-cloak>
                                        Page <span x-text="contactPage"></span> of <span x-text="contactTotalPages"></span>, showing <span x-text="contacts.length"></span> of <span x-text="contactTotalRows"></span>
                                    </span>
                                    <span x-show="isLoadingContacts" class="text-primary-500">
                                        <x-loader />
                                    </span>
                                </td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="px-3 py-3 flex items-center justify-end gap-2">
                    <div class="flex gap-1 items-center my-2">
                        <div x-show="currentContactGroup?.id" x-cloak :class="isOpenDeleteContactGroupForm ? 'z-50 border-gray-400':'' " class="relative inline-flex items-center gap-2 px-4 py-3 border border-solid border-white rounded bg-white">
                            <div x-cloak x-show="isOpenDeleteContactGroupForm" class="max-w-60 leading-tight text-sm text-gray-500">
                                Are you sure you want to delete this group? This action cannot be undone
                            </div>
                            <button x-cloak @click="handleCloseDeleteContactGroupForm()" x-show="isOpenDeleteContactGroupForm" type="button" title="Close" class="absolute -top-2 -right-2 w-6 h-6 px-0 py-0 border text-gray-500 border-solid border-gray-400 inline-flex items-center justify-center rounded-full bg-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="w-5 h-5" fill="currentColor" viewBox="0 -960 960 960">
                                    <path d="M480-424 284-228q-11 11-28 11t-28-11q-11-11-11-28t11-28l196-196-196-196q-11-11-11-28t11-28q11-11 28-11t28 11l196 196 196-196q11-11 28-11t28 11q11 11 11 28t-11 28L536-480l196 196q11 11 11 28t-11 28q-11 11-28 11t-28-11L480-424Z" />
                                </svg>
                            </button>
                            <button @click="handleDeleteContactGroup()" :disabled="isDeletingContactGroup" type="button" :class="isOpenDeleteContactGroupForm ? 'border-primary-500 bg-primary-500':'border-gray-400 bg-gray-400' " class="inline-flex gap-2 text-sm font-semibold rounded px-4 py-2 justify-center items-center leading-none no-underline border border-solid text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="w-5 h-5" fill="currentColor" viewBox="0 -960 960 960"><path d="M280-120q-33 0-56.5-23.5T200-200v-520q-17 0-28.5-11.5T160-760q0-17 11.5-28.5T200-800h160q0-17 11.5-28.5T400-840h160q17 0 28.5 11.5T600-800h160q17 0 28.5 11.5T800-760q0 17-11.5 28.5T760-720v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM400-280q17 0 28.5-11.5T440-320v-280q0-17-11.5-28.5T400-640q-17 0-28.5 11.5T360-600v280q0 17 11.5 28.5T400-280Zm160 0q17 0 28.5-11.5T600-320v-280q0-17-11.5-28.5T560-640q-17 0-28.5 11.5T520-600v280q0 17 11.5 28.5T560-280ZM280-720v520-520Z"/></svg>
                                <span x-text="isDeletingContactGroup ? 'DELETING...':'DELETE'">DELETE</span>
                            </button>
                        </div>
                        <button x-show="currentContactGroup?.id" x-cloak @click="handleOpenEditContactGroupForm()" type="button" class="inline-flex gap-2 text-sm font-semibold rounded px-4 py-2 justify-center items-center leading-none no-underline border border-solid border-primary-500 text-white bg-primary-500">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="w-5 h-5" fill="currentColor" viewBox="0 -960 960 960"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h261q20 0 30 12.5t10 27.5q0 15-10.5 27.5T460-760H200v560h560v-261q0-20 12.5-30t27.5-10q15 0 27.5 10t12.5 30v261q0 33-23.5 56.5T760-120H200Zm280-360Zm-120 80v-97q0-16 6-30.5t17-25.5l344-344q12-12 27-18t30-6q16 0 30.5 6t26.5 18l56 57q11 12 17 26.5t6 29.5q0 15-5.5 29.5T897-728L553-384q-11 11-25.5 17.5T497-360h-97q-17 0-28.5-11.5T360-400Zm481-384-56-56 56 56ZM440-440h56l232-232-28-28-29-28-231 231v57Zm260-260-29-28 29 28 28 28-28-28Z"/></svg>
                            <span>EDIT</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="importContactGroupsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="importContactGroupsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <form @submit.prevent="handleImportContactsForm($el)" action="{{ route('contacts.import.upload') }}" class="modal-content">
                    <div class="modal-header">
                        <h4 x-show="importContactStep == 'upload'" class="modal-title text-lg mt-3 font-title font-semibold select-none" id="importContactGroupsModalLabel">Import Contacts <span x-show="currentContactGroup.id?.length" x-text="'in ' + currentContactGroup.name"></span></h4>
                        <div x-show="importContactStep == 'complete' && !isImportingContacts">
                            <div class="text-lg mt-3 mb-2 font-title font-bold select-none" x-text="importContactsErrorMsg"></div>
                            <div class="text-sm text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mx-1" fill="currentColor" viewBox="0 0 448 512"><path d="M364.2 83.8c-24.4-24.4-64-24.4-88.4 0l-184 184c-42.1 42.1-42.1 110.3 0 152.4s110.3 42.1 152.4 0l152-152c10.9-10.9 28.7-10.9 39.6 0s10.9 28.7 0 39.6l-152 152c-64 64-167.6 64-231.6 0s-64-167.6 0-231.6l184-184c46.3-46.3 121.3-46.3 167.6 0s46.3 121.3 0 167.6l-176 176c-28.6 28.6-75 28.6-103.6 0s-28.6-75 0-103.6l144-144c10.9-10.9 28.7-10.9 39.6 0s10.9 28.7 0 39.6l-144 144c-6.7 6.7-6.7 17.7 0 24.4s17.7 6.7 24.4 0l176-176c24.4-24.4 24.4-64 0-88.4z"/></svg>
                                <span x-show="currentContactGroup?.name" x-text="'From: ' + currentContactGroup.name"></span>
                            </div>
                        </div>
                        <button type="button" @click.prevent="closeImportContactsModal()" title="Close" data-not-bs-dismiss="modal" aria-label="Close" class="absolute -top-2 -right-1 w-7 h-7 px-0 py-0 border text-gray-500 border-solid border-gray-400 inline-flex items-center justify-center rounded-full bg-white">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="w-5 h-5" fill="currentColor" viewBox="0 -960 960 960">
                                <path d="M480-424 284-228q-11 11-28 11t-28-11q-11-11-11-28t11-28l196-196-196-196q-11-11-11-28t11-28q11-11 28-11t28 11l196 196 196-196q11-11 28-11t28 11q11 11 11 28t-11 28L536-480l196 196q11 11 11 28t-11 28q-11 11-28 11t-28-11L480-424Z" />
                            </svg>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div x-show="importContactStep == 'upload'">
                            <input type="file" id="import_contacts_file" name="importFile" @change="handleImportContactsFile($event)" accept=".xls,.xlsx" class="hidden" />
                            <label for="import_contacts_file" :class="{'opacity-50 pointer-events-none': isImportingContacts}" class="cursor-pointer inline-flex w-full mb-1 gap-3 font-title text font-semibold rounded px-4 py-3 justify-center items-center leading-none no-underline border border-solid border-primary-500 text-white bg-primary-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" width="24" height="24" fill="currentColor" viewBox="0 -960 960 960"><path d="M440-367v127q0 17 11.5 28.5T480-200q17 0 28.5-11.5T520-240v-127l36 36q6 6 13.5 9t15 2.5q7.5-.5 14.5-3.5t13-9q11-12 11.5-28T612-388L508-492q-6-6-13-8.5t-15-2.5q-8 0-15 2.5t-13 8.5L348-388q-12 12-11.5 28t12.5 28q12 11 28 11.5t28-11.5l35-35ZM240-80q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h287q16 0 30.5 6t25.5 17l194 194q11 11 17 25.5t6 30.5v447q0 33-23.5 56.5T720-80H240Zm280-560v-160H240v640h480v-440H560q-17 0-28.5-11.5T520-640ZM240-800v200-200 640-640Z"/></svg>
                                <span>UPLOAD CONTACTS</span>
                            </label>
                            <p class="mb-8 text-center text-sm text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mx-1" fill="currentColor" viewBox="0 0 448 512"><path d="M364.2 83.8c-24.4-24.4-64-24.4-88.4 0l-184 184c-42.1 42.1-42.1 110.3 0 152.4s110.3 42.1 152.4 0l152-152c10.9-10.9 28.7-10.9 39.6 0s10.9 28.7 0 39.6l-152 152c-64 64-167.6 64-231.6 0s-64-167.6 0-231.6l184-184c46.3-46.3 121.3-46.3 167.6 0s46.3 121.3 0 167.6l-176 176c-28.6 28.6-75 28.6-103.6 0s-28.6-75 0-103.6l144-144c10.9-10.9 28.7-10.9 39.6 0s10.9 28.7 0 39.6l-144 144c-6.7 6.7-6.7 17.7 0 24.4s17.7 6.7 24.4 0l176-176c24.4-24.4 24.4-64 0-88.4z"/></svg>
                                <span x-text="importContactsFilename.length ? importContactsFilename : 'No file uploaded'">No file uploaded</span>
                                <br><span x-show="importContactsFilename.length && currentContactGroup?.id" x-text="currentContactGroup.name"></span>
                            </p>
                        </div>
                        <div x-show="importContactStep == 'hasNewPhoneNumbers'">
                            <div class="flex gap-2 mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="flex-none text-primary-500" width="30" height="30" fill="none" viewBox="0 0 24 24">
                                    <path fill="currentColor" d="M13 9a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM12 11.75a.75.75 0 0 1 .75.75v5a.75.75 0 1 1-1.5 0v-5a.75.75 0 0 1 .75-.75Z"/>
                                    <path fill="currentColor" fill-rule="evenodd" d="M14.27 3.993c-1.092-1.598-3.448-1.598-4.54 0l-.432.632a75.95 75.95 0 0 0-6.944 12.563l-.09.208a2.511 2.511 0 0 0 2.024 3.497 69.43 69.43 0 0 0 15.424 0 2.511 2.511 0 0 0 2.024-3.497l-.09-.208a75.951 75.951 0 0 0-6.944-12.563l-.432-.632Zm-3.302.846a1.25 1.25 0 0 1 2.064 0l.432.632a74.444 74.444 0 0 1 6.806 12.315l.09.208a1.011 1.011 0 0 1-.814 1.408c-5.015.56-10.077.56-15.092 0a1.011 1.011 0 0 1-.815-1.408l.09-.208a74.45 74.45 0 0 1 6.807-12.315l.432-.632Z" clip-rule="evenodd"/>
                                </svg>
                                <div class="">
                                    <span class="block mb-1 text-lg text-primary-500 font-title font-semibold select-none">
                                        (<span x-text="countNewPhoneNumbers"></span>) Contact<span x-show="countNewPhoneNumbers > 1">s</span> already exist. But, phone number<span x-show="countNewPhoneNumbers > 1">s</span> do not match
                                    </span>
                                    <div class="text-sm text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mx-1" fill="currentColor" viewBox="0 0 448 512"><path d="M364.2 83.8c-24.4-24.4-64-24.4-88.4 0l-184 184c-42.1 42.1-42.1 110.3 0 152.4s110.3 42.1 152.4 0l152-152c10.9-10.9 28.7-10.9 39.6 0s10.9 28.7 0 39.6l-152 152c-64 64-167.6 64-231.6 0s-64-167.6 0-231.6l184-184c46.3-46.3 121.3-46.3 167.6 0s46.3 121.3 0 167.6l-176 176c-28.6 28.6-75 28.6-103.6 0s-28.6-75 0-103.6l144-144c10.9-10.9 28.7-10.9 39.6 0s10.9 28.7 0 39.6l-144 144c-6.7 6.7-6.7 17.7 0 24.4s17.7 6.7 24.4 0l176-176c24.4-24.4 24.4-64 0-88.4z"/></svg>
                                        <span x-show="currentContactGroup?.name" x-text="'From: ' + currentContactGroup.name"></span>
                                    </div>
                                </div>
                            </div>
                            <template x-if="importContactStep == 'hasNewPhoneNumbers' && !isImportingContacts">
                                <div class="">
                                    <div class="flex gap-3 mb-2">
                                        <input type="radio" name="newPhoneNumberAction" id="new-phone-number-action-update" value="update" required class="flex-none w-5 h-5 my-1 border-solid text-gray-900 focus:ring-gray-800 border-gray-500" />
                                        <label class="leading-tight cursor-pointer" for="new-phone-number-action-update">Update phone numbers to the new numbers from <span x-text="importContactsFilename"></span></label>
                                    </div>
                                    <div class="flex gap-3 mb-2">
                                        <input type="radio" name="newPhoneNumberAction" id="new-phone-number-action-ignore" value="ignore" required class="flex-none w-5 h-5 my-1 border-solid text-gray-900 focus:ring-gray-800 border-gray-500" />
                                        <label class="leading-tight cursor-pointer" for="new-phone-number-action-ignore">Keep existing phone numbers for contacts. Do not update</label>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <p x-show="importContactsErrorMsg.length && !isImportingContacts && importContactStep != 'complete'" x-text="importContactsErrorMsg" class="font-semibold text-center mb-0"></p>
                        <div x-show="isImportingContacts" class="text-center">
                            <small>Do not close browser, while import is processing</small>
                            <div class="my-2">
                                <x-loader class="w-10 h-10 text-primary-500" />
                            </div>
                        </div>
                        <button type="submit" x-show="!isImportingContacts && importContactStep != 'complete'" :disabled="importContactsDisabled" class="w-full my-2 py-2 font-medium rounded text-center border border-solid border-black bg-black text-white disabled:text-black disabled:bg-white">
                            <span x-show="importContactStep == 'upload'">CREATE CONTACTS & ADD TO GROUP</span>
                            <span x-show="importContactStep == 'hasNewPhoneNumbers'">CONTINUE</span>
                        </button>
                    </div>
                    <!-- <div class="modal-footer"></div> -->
                </form>
            </div>
        </div>
        <span x-show="isOpenNewContactGroupForm || isOpenEditContactGroupForm || isOpenDeleteContactGroupForm" x-cloak x-transition.opacity class="fixed top-0 right-0 bottom-0 left-0 bg-black/50"></span>
    </div>
    <x-slot name="headScript">
        <script type="text/javascript">
            var CONTACT_GROUPS_ROUTE_INDEX = "{{ route('contact-groups.index') }}";
            var CONTACT_GROUPS_ROUTE_DELETE = "{{ route('contact-groups.delete') }}";
            var CONTACT_ROUTE_INDEX = "{{ route('contacts.index') }}";
        </script>
    </x-slot>
</x-admin-layout>