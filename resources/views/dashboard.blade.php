<x-admin-layout activity="1" autosize="1" flatpickr="1" toastify="1">
    <div x-data="activityData" class="max-w-screen-xl mx-auto px-4 mb-8">

        <div class="flex flex-wrap justify-between gap-3 mt-4 mb-3">
            <div class="inline-flex flex-wrap gap-3">
                <x-header.nav-btn active="1">ACTIVITY</x-header.nav-btn>
                <x-header.nav-btn :href="route('contact-groups.index')">GROUPS</x-header.nav-btn>
                <x-header.nav-btn :href="route('contacts.index')">CONTACTS</x-header.nav-btn>
                <x-header.nav-btn :href="route('templates.index')">TEMPLATES</x-header.nav-btn>
                <x-header.add-btn data-bs-toggle="modal" data-bs-target="#newSmsModal">NEW SMS</x-header.add-btn>
            </div>
            <div class="">
                <x-profile-switcher />
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 my-3">
            <div class="inline-block relative">
                <input type="text" placeholder="User/group" x-model="keywordRecipient" @input.debounce.500ms="handleKeywordChange()" class="py-2 pl-8 font-title text-sm rounded border-gray-400 border-solid focus:border-primary-500 focus:ring-primary-400" />
                <span class="absolute left-0 top-0 bottom-0 pointer-events-none px-3 inline-flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" class="" fill="none"><path fill="#515151" d="M10 5.067C10 2.273 7.757 0 5 0S0 2.273 0 5.067c0 2.793 2.243 5.066 5 5.066s5-2.273 5-5.066Zm-5 3.8c-2.068 0-3.75-1.705-3.75-3.8 0-2.096 1.682-3.8 3.75-3.8s3.75 1.704 3.75 3.8c0 2.095-1.682 3.8-3.75 3.8Zm7.317 3.614a.619.619 0 0 1-.884 0L8.507 9.516c.326-.265.623-.565.884-.896l2.926 2.966a.64.64 0 0 1 0 .895Z"/></svg>
                </span>
            </div>
            <select x-model="filterStatus" @change="handleFilterStatusChange()" title="Status" class="py-2 font-title text-sm select-none rounded border-gray-400 border-solid focus:border-primary-500 focus:ring-primary-400">
                <option value="">All</option>
                <option value="delivered">Delivered</option>
                <option value="pending">Pending</option>
                <option value="soft-bounce">Soft Bounce</option>
                <option value="hard-bounce">Hard Bounce</option>
                <option value="sent">Sent</option>
            </select>
            <div class="inline-block relative">
                <input type="text" x-model="filterStartDate" id="filterStartDateEl" placeholder="Date/Time From" class="py-2 w-52 font-title text-sm rounded border-gray-400 border-solid focus:border-primary-500 focus:ring-primary-400" />
                <span class="absolute right-0 top-0 bottom-0 pointer-events-none px-3 inline-flex gap-1 items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16" fill="none"><path fill="#515151" fill-rule="evenodd" d="M4.455 1.273V.955A.957.957 0 0 0 3.5 0h-.636a.957.957 0 0 0-.955.955v.318h2.546Zm7.636 0V.955A.957.957 0 0 0 11.136 0H10.5a.957.957 0 0 0-.955.955v.318h2.546ZM14 4.455V3.5c0-.877-.714-1.59-1.59-1.59H1.59C.715 1.91 0 2.622 0 3.5v.955h14ZM0 13.682V5.09h14v8.59c0 .878-.714 1.592-1.59 1.592H1.59c-.876 0-1.59-.714-1.59-1.591Zm3.66-.637a.796.796 0 0 0 .795-.795v-.955a.796.796 0 0 0-.796-.795h-.954a.796.796 0 0 0-.796.795v.955c0 .438.357.795.796.795h.954Zm0-3.818a.796.796 0 0 0 .795-.795v-.955a.796.796 0 0 0-.796-.795h-.954a.796.796 0 0 0-.796.795v.955c0 .438.357.795.796.795h.954Zm3.817 3.818a.796.796 0 0 0 .796-.795v-.955a.796.796 0 0 0-.796-.795h-.954a.796.796 0 0 0-.796.795v.955c0 .438.357.795.796.795h.954Zm0-3.818a.796.796 0 0 0 .796-.795v-.955a.796.796 0 0 0-.796-.795h-.954a.796.796 0 0 0-.796.795v.955c0 .438.357.795.796.795h.954Zm3.819 3.818a.796.796 0 0 0 .795-.795v-.955a.796.796 0 0 0-.795-.795h-.955a.796.796 0 0 0-.796.795v.955c0 .438.357.795.796.795h.955Zm0-3.818a.796.796 0 0 0 .795-.795v-.955a.796.796 0 0 0-.795-.795h-.955a.796.796 0 0 0-.796.795v.955c0 .438.357.795.796.795h.955Z" clip-rule="evenodd"/></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="16" fill="none"><path fill="#515151" d="M7.5 15c4.136 0 7.5-3.364 7.5-7.5S11.636 0 7.5 0 0 3.364 0 7.5 3.364 15 7.5 15ZM7.098 3.214a.401.401 0 1 1 .804 0v3.884h2.943c.22 0 .402.18.402.402a.403.403 0 0 1-.402.402H7.5a.401.401 0 0 1-.402-.402V3.214Z"/></svg>
                </span>
            </div>
            <div class="inline-block relative">
                <input type="text" x-model="filterEndDate" id="filterEndDateEl" placeholder="Date/Time To" class="py-2 w-52 font-title text-sm rounded border-gray-400 border-solid focus:border-primary-500 focus:ring-primary-400" />
                <span class="absolute right-0 top-0 bottom-0 pointer-events-none px-3 inline-flex gap-1 items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16" fill="none"><path fill="#515151" fill-rule="evenodd" d="M4.455 1.273V.955A.957.957 0 0 0 3.5 0h-.636a.957.957 0 0 0-.955.955v.318h2.546Zm7.636 0V.955A.957.957 0 0 0 11.136 0H10.5a.957.957 0 0 0-.955.955v.318h2.546ZM14 4.455V3.5c0-.877-.714-1.59-1.59-1.59H1.59C.715 1.91 0 2.622 0 3.5v.955h14ZM0 13.682V5.09h14v8.59c0 .878-.714 1.592-1.59 1.592H1.59c-.876 0-1.59-.714-1.59-1.591Zm3.66-.637a.796.796 0 0 0 .795-.795v-.955a.796.796 0 0 0-.796-.795h-.954a.796.796 0 0 0-.796.795v.955c0 .438.357.795.796.795h.954Zm0-3.818a.796.796 0 0 0 .795-.795v-.955a.796.796 0 0 0-.796-.795h-.954a.796.796 0 0 0-.796.795v.955c0 .438.357.795.796.795h.954Zm3.817 3.818a.796.796 0 0 0 .796-.795v-.955a.796.796 0 0 0-.796-.795h-.954a.796.796 0 0 0-.796.795v.955c0 .438.357.795.796.795h.954Zm0-3.818a.796.796 0 0 0 .796-.795v-.955a.796.796 0 0 0-.796-.795h-.954a.796.796 0 0 0-.796.795v.955c0 .438.357.795.796.795h.954Zm3.819 3.818a.796.796 0 0 0 .795-.795v-.955a.796.796 0 0 0-.795-.795h-.955a.796.796 0 0 0-.796.795v.955c0 .438.357.795.796.795h.955Zm0-3.818a.796.796 0 0 0 .795-.795v-.955a.796.796 0 0 0-.795-.795h-.955a.796.796 0 0 0-.796.795v.955c0 .438.357.795.796.795h.955Z" clip-rule="evenodd"/></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="16" fill="none"><path fill="#515151" d="M7.5 15c4.136 0 7.5-3.364 7.5-7.5S11.636 0 7.5 0 0 3.364 0 7.5 3.364 15 7.5 15ZM7.098 3.214a.401.401 0 1 1 .804 0v3.884h2.943c.22 0 .402.18.402.402a.403.403 0 0 1-.402.402H7.5a.401.401 0 0 1-.402-.402V3.214Z"/></svg>
                </span>
            </div>
            <select x-model="filterFolder" @change="handleFilterChange()" class="py-2 font-title text-sm select-none rounded border-gray-400 border-solid focus:border-primary-500 focus:ring-primary-400">
                <option value="">All SMS</option>
                <option value="outbox">Outbox</option>
                <option value="inbox">Inbox</option>
            </select>
            <div class="inline-block relative">
                <input type="text" x-model="keyword" @input.debounce.500ms="handleKeywordChange()" placeholder="Search" class="py-2 pl-8 font-title text-sm rounded border-gray-400 border-solid focus:border-primary-500 focus:ring-primary-400" />
                <span class="absolute left-0 top-0 bottom-0 pointer-events-none px-3 inline-flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" class="" fill="none"><path fill="#515151" d="M10 5.067C10 2.273 7.757 0 5 0S0 2.273 0 5.067c0 2.793 2.243 5.066 5 5.066s5-2.273 5-5.066Zm-5 3.8c-2.068 0-3.75-1.705-3.75-3.8 0-2.096 1.682-3.8 3.75-3.8s3.75 1.704 3.75 3.8c0 2.095-1.682 3.8-3.75 3.8Zm7.317 3.614a.619.619 0 0 1-.884 0L8.507 9.516c.326-.265.623-.565.884-.896l2.926 2.966a.64.64 0 0 1 0 .895Z"/></svg>
                </span>
            </div>
            <a href="{{ route('sms.index', ['export'=> 1]) }}" title="Export" target="_blank" rel="noopener noreferrer nofollow" class="inline-flex gap-2 font-title text-sm font-semibold rounded px-2 py-2 justify-center items-center leading-none no-underline border border-solid border-gray-600 text-white bg-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" width="24" height="24" fill="currentColor" viewBox="0 -960 960 960"><path d="M480-337q-8 0-15-2.5t-13-8.5L308-492q-12-12-11.5-28t11.5-28q12-12 28.5-12.5T365-549l75 75v-286q0-17 11.5-28.5T480-800q17 0 28.5 11.5T520-760v286l75-75q12-12 28.5-11.5T652-548q11 12 11.5 28T652-492L508-348q-6 6-13 8.5t-15 2.5ZM240-160q-33 0-56.5-23.5T160-240v-80q0-17 11.5-28.5T200-360q17 0 28.5 11.5T240-320v80h480v-80q0-17 11.5-28.5T760-360q17 0 28.5 11.5T800-320v80q0 33-23.5 56.5T720-160H240Z"/></svg>
            </a>
            <button type="button" class="web-push-btn hidden inline-flex gap-2 font-title text-sm font-semibold rounded px-2 py-2 justify-center items-center leading-none no-underline border border-solid border-gray-600 text-white bg-gray-700" title="Enable Web Push Notifications">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" width="24" height="24" fill="currentColor" viewBox="0 0 448 512"><path d="M224 0c-17.7 0-32 14.3-32 32l0 19.2C119 66 64 130.6 64 208l0 25.4c0 45.4-15.5 89.5-43.8 124.9L5.3 377c-5.8 7.2-6.9 17.1-2.9 25.4S14.8 416 24 416l400 0c9.2 0 17.6-5.3 21.6-13.6s2.9-18.2-2.9-25.4l-14.9-18.6C399.5 322.9 384 278.8 384 233.4l0-25.4c0-77.4-55-142-128-156.8L256 32c0-17.7-14.3-32-32-32zm0 96c61.9 0 112 50.1 112 112l0 25.4c0 47.9 13.9 94.6 39.7 134.6L72.3 368C98.1 328 112 281.3 112 233.4l0-25.4c0-61.9 50.1-112 112-112zm64 352l-64 0-64 0c0 17 6.7 33.3 18.7 45.3s28.3 18.7 45.3 18.7s33.3-6.7 45.3-18.7s18.7-28.3 18.7-45.3z"/></svg>
            </button>
        </div>

        <div class="overflow-auto">
            <table class="w-full bg-white shadow">
                <thead>
                    <tr>
                        {{-- <th class="text-white bg-black"></th> --}}
                        <th class="px-4 py-2 font-semibold text-white bg-black">Recipient</th>
                        <th class="px-4 py-2 font-semibold text-white bg-black">Mobile</th>
                        <th class="px-4 py-2 font-semibold text-white bg-black">From</th>
                        <th class="px-4 py-2 font-semibold text-white bg-black">Status</th>
                        <th class="px-4 py-2 font-semibold text-white bg-black">Date/Time</th>
                        <th class="px-4 py-2 font-semibold text-white bg-black text-center">Out/In</th>
                        <th class="px-4 py-2 font-semibold text-white bg-black">Message</th>
                        <th class="px-4 py-2 font-semibold text-white bg-black"></th>
                    </tr>
                </thead>
                <tbody class="border border-t-0 border-solid border-gray-200">
                    <template x-for="sms in items" :key="sms.id">
                        <tr>
                            {{-- <td class="px-4 py-2 border-0 border-b border-solid border-gray-300 text-sm"></td> --}}
                            <td class="px-4 py-2 border-0 border-b border-solid border-gray-300 text-sm" x-text="sms.recipient"></td>
                            <td class="px-4 py-2 border-0 border-b border-solid border-gray-300 text-sm">
                                <div x-text="sms.recipient_name"></div>
                                <div x-text="sms.to"></div>
                            </td>
                            <td class="px-4 py-2 border-0 border-b border-solid border-gray-300 text-sm">
                                <div class="text-sm">
                                    <span x-text="sms?.from_name"></span>
                                    <span x-text="sms?.from_lastname"></span>
                                </div>
                                <div x-text="sms?.from_number"></div>
                            </td>
                            <td class="px-4 py-2 border-0 border-b border-solid border-gray-300 text-sm" x-text="sms.status"></td>
                            <td class="px-4 py-2 border-0 border-b border-solid border-gray-300 text-sm" x-text="sms.send_at"></td>
                            <td class="px-4 py-2 border-0 border-b border-solid border-gray-300 text-sm text-center">
                                <svg x-show="sms.folder == 'outbox'" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#22c55e" class="bi bi-arrow-right-circle-fill" viewBox="0 0 16 16">
                                    <path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0M4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5z"/>
                                </svg>
                                <svg x-show="sms.folder == 'inbox'" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#eab308" class="bi bi-arrow-left-circle-fill" viewBox="0 0 16 16">
                                    <path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0m3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z"/>
                                </svg>
                            </td>
                            <td class="px-4 py-2 border-0 border-b border-solid border-gray-300 text-sm">
                                <span x-text="sms.message"></span>
                                {{-- <a href="#!" class="text-primary-500">View More</a> --}}
                            </td>
                            <td class="px-4 py-2 border-0 border-b border-solid border-gray-300 text-sm"></td>
                        </tr>
                    </template>
                    <tr>
                        <td colspan="9" class="px-4 py-2 text-sm text-center">
                            <span x-show="isLoadingItems">Loading, please wait...</span>
                            <span x-show="!isLoadingItems" x-cloak>
                                Page <span x-text="page"></span> of <span x-text="totalPages"></span>, showing <span x-text="items.length"></span> of <span x-text="totalItems"></span>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>


        <!-- modal start -->
        <div class="modal fade" id="newSmsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="newSmsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" style="--bs-modal-width:920px">
                <form @submit.prevent="handleNewSmsForm($el)" @change="sendSmsFormChanged()" x-ref="newSmsFormRef" action="{{ route('sms.store') }}" class="modal-content">
                    <div class="modal-body">
                        <div class="flex flex-wrap mb-2">
                            <div class="w-12 md:w-8/12">
                                <div class="mb-4">
                                    <h4 class="modal-title text-lg font-title font-semibold" id="newSmsModalLabel">
                                        <span>New SMS</span>
                                    </h4>
                                </div>
                                <div class="flex flex-wrap -mx-1">
                                    <div class="w-full md:w-6/12 px-1 mb-3">
                                        <div>Profile*</div>
                                        <select name="profile_id" class="w-full py-2 text-sm rounded border-gray-400 border-solid focus:border-gray-400 focus:ring-0">
                                            @if(!empty($profiles))
                                                @foreach($profiles as $_profile_id => $_profile)
                                                    <option value="{{ $_profile_id }}">{{ $_profile['name'] }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="w-full md:w-6/12 px-1 mb-3">
                                        <div>Date/Time*</div>
                                        <div class="relative">
                                            <input type="text" x-model="send_at" id="send_at" name="send_at" placeholder="Date/Time" class="py-2 text-sm w-full font-title rounded border-gray-400 border-solid focus:border-primary-500 focus:ring-primary-400" />
                                            <span class="absolute right-0 top-0 bottom-0 pointer-events-none px-3 inline-flex gap-1 items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16" fill="none"><path fill="#515151" fill-rule="evenodd" d="M4.455 1.273V.955A.957.957 0 0 0 3.5 0h-.636a.957.957 0 0 0-.955.955v.318h2.546Zm7.636 0V.955A.957.957 0 0 0 11.136 0H10.5a.957.957 0 0 0-.955.955v.318h2.546ZM14 4.455V3.5c0-.877-.714-1.59-1.59-1.59H1.59C.715 1.91 0 2.622 0 3.5v.955h14ZM0 13.682V5.09h14v8.59c0 .878-.714 1.592-1.59 1.592H1.59c-.876 0-1.59-.714-1.59-1.591Zm3.66-.637a.796.796 0 0 0 .795-.795v-.955a.796.796 0 0 0-.796-.795h-.954a.796.796 0 0 0-.796.795v.955c0 .438.357.795.796.795h.954Zm0-3.818a.796.796 0 0 0 .795-.795v-.955a.796.796 0 0 0-.796-.795h-.954a.796.796 0 0 0-.796.795v.955c0 .438.357.795.796.795h.954Zm3.817 3.818a.796.796 0 0 0 .796-.795v-.955a.796.796 0 0 0-.796-.795h-.954a.796.796 0 0 0-.796.795v.955c0 .438.357.795.796.795h.954Zm0-3.818a.796.796 0 0 0 .796-.795v-.955a.796.796 0 0 0-.796-.795h-.954a.796.796 0 0 0-.796.795v.955c0 .438.357.795.796.795h.954Zm3.819 3.818a.796.796 0 0 0 .795-.795v-.955a.796.796 0 0 0-.795-.795h-.955a.796.796 0 0 0-.796.795v.955c0 .438.357.795.796.795h.955Zm0-3.818a.796.796 0 0 0 .795-.795v-.955a.796.796 0 0 0-.795-.795h-.955a.796.796 0 0 0-.796.795v.955c0 .438.357.795.796.795h.955Z" clip-rule="evenodd"/></svg>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="16" fill="none"><path fill="#515151" d="M7.5 15c4.136 0 7.5-3.364 7.5-7.5S11.636 0 7.5 0 0 3.364 0 7.5 3.364 15 7.5 15ZM7.098 3.214a.401.401 0 1 1 .804 0v3.884h2.943c.22 0 .402.18.402.402a.403.403 0 0 1-.402.402H7.5a.401.401 0 0 1-.402-.402V3.214Z"/></svg>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="w-full md:w-6/12 px-1 mb-3">
                                        <div>Template</div>
                                        <select name="template_id" x-model="selectedTemplateId" @change="handleTemplateSelected()" class="w-full py-2 text-sm rounded border-gray-400 border-solid focus:border-primary-500 focus:ring-primary-400">
                                            <option value="">Select Template</option>
                                            <template x-for="template in templates" :key="template.id">
                                                <option :value="template.id" x-text="template.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="w-full md:w-6/12 px-1 mb-3">
                                        <div>Sender Number</div>
                                        <select name="from" required class="w-full py-2 text-sm rounded border-gray-400 border-solid focus:border-primary-500 focus:ring-primary-400">
                                            @if(is_array($phoneNumbers))
                                                @if(count($phoneNumbers) > 1)
                                                    <option value="">Select sender number</option>
                                                @endif
                                                @foreach($phoneNumbers as $number_key => $number)
                                                    <option value="{{ $number['phone'] }}">{{ $number['phone'] }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    {{-- <div class="w-full md:w-6/12 px-1 mb-3">
                                        <div>Title/subject</div>
                                        <input type="text" name="title" placeholder="Type Subject" class="py-2 text-sm w-full font-title rounded border-gray-400 border-solid focus:border-primary-500 focus:ring-primary-400" />
                                    </div> --}}
                                    <div class="w-full px-1 mb-3">
                                        <div class="flex gap-6">
                                            <label class="inline-flex gap-2 items-center">
                                                <input type="radio" name="sending" value="adhoc" x-model="sending" class="flex-none w-5 h-5 my-1 border-solid text-primary-500 focus:ring-primary-400 border-gray-500" />
                                                <span class="leading-tight cursor-pointer">Send to Adhoc number</span>
                                            </label>
                                            <label class="inline-flex gap-2 items-center">
                                                <input type="radio" name="sending" value="contact" x-model="sending" checked class="flex-none w-5 h-5 my-1 border-solid text-primary-500 focus:ring-primary-400 border-gray-500" />
                                                <span class="leading-tight cursor-pointer">Send to contact</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div x-show="sending == 'adhoc'" class="w-full sm:w-6/12 lg:w-4/12 px-1 mb-3">
                                        <div>Country</div>
                                        <select name="country" :required="sending == 'adhoc'" class="w-full py-2 text-sm rounded border-gray-400 border-solid focus:border-primary-500 focus:ring-primary-400">
                                            <option value="">Select Country</option>
                                            @if(!empty($countries) && is_array($countries))
                                                @foreach($countries as $cntry_key => $cntry_value)
                                                    <option value="{{ $cntry_key }}">{{ $cntry_value }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div x-show="sending == 'adhoc'" class="w-full sm:w-6/12 lg:w-8/12 px-1 mb-3">
                                        <div>Adhoc numbers <span class="text-sm">(with country code & separate by comma)</span></div>
                                        <input type="text" name="adhoc_numbers" :required="sending == 'adhoc'" placeholder="61430253080" class="py-2 text-sm w-full font-title rounded border-gray-400 border-solid focus:border-primary-500 focus:ring-primary-400" />
                                    </div>
                                    <div x-show="sending == 'contact'" @click.away="isContactGroupDropdownOpen = false" class="w-full px-1 mb-3">
                                        <div>Recipient*</div>
                                        <div class="relative">
                                            <input type="text" x-model="contactGroupKeyword" @focus="handleContactGroupKeywordFocus()" @input.debounce.750ms="handleContactGroupKeywordChange()" @keydown.enter.prevent.stop="handleFormEnter()" @keyup.enter.debounce.750ms="handleContactGroupClickEnter()" spellcheck="false" placeholder="Type Recipient" class="py-2 pl-8 font-title w-full text-sm rounded border-gray-400 border-solid focus:border-primary-500 focus:ring-primary-400" />
                                            <span class="absolute left-0 top-0 bottom-0 pointer-events-none w-8 inline-flex items-center justify-center">
                                                <span x-show="isLoadingContactGroups" class="absolute top-0 left-0 bottom-0 right-0 inline-flex items-center justify-center text-primary-500" x-transition.opacity><x-loader class="w-4 h-4" /></span>
                                                <svg x-show="!isLoadingContactGroups" x-transition.opacity xmlns="http://www.w3.org/2000/svg" width="13" height="13" class="w-3 h-3" fill="none"><path fill="#515151" d="M10 5.067C10 2.273 7.757 0 5 0S0 2.273 0 5.067c0 2.793 2.243 5.066 5 5.066s5-2.273 5-5.066Zm-5 3.8c-2.068 0-3.75-1.705-3.75-3.8 0-2.096 1.682-3.8 3.75-3.8s3.75 1.704 3.75 3.8c0 2.095-1.682 3.8-3.75 3.8Zm7.317 3.614a.619.619 0 0 1-.884 0L8.507 9.516c.326-.265.623-.565.884-.896l2.926 2.966a.64.64 0 0 1 0 .895Z"/></svg>
                                            </span>
                                        </div>
                                        <div class="relative">
                                            <div x-show="isContactGroupDropdownOpen" x-transition.scale.top class="absolute min-h-12 max-h-40 w-full max-w-sm overflow-y-auto bg-white rounded border border-solid border-gray-200 shadow-lg">
                                                <div x-show="isLoadingContactGroups" class="absolute text-center py-2 top-0 bottom-0 left-0 right-0 backdrop-blur-sm">
                                                    <x-loader />
                                                </div>
                                                <div x-show="!isLoadingContactGroups && !contactGroups?.length" class="px-3 py-2 text-gray-400">
                                                    Nothing found in groups
                                                </div>
                                                <div x-show="contactGroups?.length">
                                                    <template x-for="contactGroup in contactGroups" :key="contactGroup.id">
                                                        <button type="button" @click="handleContactGroupDropdownClick(contactGroup)" class="w-full px-3 py-2 leading-tight inline-flex items-center justify-between truncate border-0 bg-transparent hover:bg-gray-100">
                                                            <span x-text="contactGroup.name" class="grow text-left truncate"></span>
                                                            <span x-show="contactGroup?.added" class="inline-flex flex-none items-center text-gray-400">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="w-4 h-4" fill="currentColor" viewBox="0 -960 960 960">
                                                                    <path d="M480-424 284-228q-11 11-28 11t-28-11q-11-11-11-28t11-28l196-196-196-196q-11-11-11-28t11-28q11-11 28-11t28 11l196 196 196-196q11-11 28-11t28 11q11 11 11 28t-11 28L536-480l196 196q11 11 11 28t-11 28q-11 11-28 11t-28-11L480-424Z" />
                                                                </svg>
                                                            </span>
                                                        </button>
                                                    </template>
                                                </div>
                                                <div x-show="!isLoadingContactGroups && !contactGroupContacts?.length" class="px-3 py-2 text-gray-400">
                                                    Nothing found in contacts
                                                </div>
                                                <div x-show="contactGroupContacts?.length">
                                                    <template x-for="contactGroupContact in contactGroupContacts" :key="contactGroupContact.id">
                                                        <button type="button" @click="handleContactGroupContactDropdownClick(contactGroupContact)" class="w-full px-3 py-2 leading-tight inline-flex items-center justify-between truncate border-0 bg-transparent hover:bg-gray-100">
                                                            <span x-text="contactGroupContact.name" class="grow text-left truncate"></span>
                                                            <span x-show="contactGroupContact?.added" class="inline-flex flex-none items-center text-gray-400">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="w-4 h-4" fill="currentColor" viewBox="0 -960 960 960">
                                                                    <path d="M480-424 284-228q-11 11-28 11t-28-11q-11-11-11-28t11-28l196-196-196-196q-11-11-11-28t11-28q11-11 28-11t28 11l196 196 196-196q11-11 28-11t28 11q11 11 11 28t-11 28L536-480l196 196q11 11 11 28t-11 28q-11 11-28 11t-28-11L480-424Z" />
                                                                </svg>
                                                            </span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap gap-2" :class="{'mt-2' : (selectedContactGroups.length || selectedContactGroupContacts.length)}">
                                            <template x-for="selectedContactGroup in selectedContactGroups" :key="selectedContactGroup.id">
                                                <div class="inline-flex max-w-56 truncate rounded pl-3 text-sm leading-tight text-gray-600 border border-solid border-gray-200 bg-gray-100">
                                                    <input type="hidden" name="contact_group_uid[]" :value="selectedContactGroup.uid" />
                                                    <span class="self-center truncate" x-text="selectedContactGroup.name"></span>
                                                    <button type="button" @click="handleRemoveSelectedContactGroup(selectedContactGroup)" title="Clear" class="px-2 py-2 text-gray-600 border-0 bg-transparent">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="w-4 h-4" fill="currentColor" viewBox="0 -960 960 960">
                                                            <path d="M480-424 284-228q-11 11-28 11t-28-11q-11-11-11-28t11-28l196-196-196-196q-11-11-11-28t11-28q11-11 28-11t28 11l196 196 196-196q11-11 28-11t28 11q11 11 11 28t-11 28L536-480l196 196q11 11 11 28t-11 28q-11 11-28 11t-28-11L480-424Z" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>
                                            <template x-for="selectedContactGroupContact in selectedContactGroupContacts" :key="selectedContactGroupContact.id">
                                                <div class="inline-flex max-w-56 truncate rounded pl-3 text-sm leading-tight text-gray-600 border border-solid border-gray-200 bg-gray-100">
                                                    <input type="hidden" name="contact_id[]" :value="selectedContactGroupContact.id" />
                                                    <span class="self-center truncate" x-text="selectedContactGroupContact.name"></span>
                                                    <button type="button" @click="handleRemoveSelectedContactGroupContact(selectedContactGroupContact)" title="Clear" class="px-2 py-2 text-gray-600 border-0 bg-transparent">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="w-4 h-4" fill="currentColor" viewBox="0 -960 960 960">
                                                            <path d="M480-424 284-228q-11 11-28 11t-28-11q-11-11-11-28t11-28l196-196-196-196q-11-11-11-28t11-28q11-11 28-11t28 11l196 196 196-196q11-11 28-11t28 11q11 11 11 28t-11 28L536-480l196 196q11 11 11 28t-11 28q-11 11-28 11t-28-11L480-424Z" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
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
                                    <div class="w-full px-2 mb-3">
                                        <div class="flex flex-wrap gap-6 items-center">
                                            <label class="inline-flex gap-2 cursor-pointer items-center">
                                                <input type="checkbox" name="isTesting" value="YES" class="border-solid rounded bg-gray-200 text-primary-500 focus:ring-primary-400" />
                                                <span class="select-none">Is Testing</span>
                                            </label>
                                            <?php if (!empty($_GET['test-quick-send'])) { ?>
                                                <label class="inline-flex gap-2 cursor-pointer items-center">
                                                    <input type="checkbox" name="isQuick" value="YES" class="border-solid rounded bg-gray-200 text-primary-500 focus:ring-primary-400" />
                                                    <span class="select-none">Quick Send</span>
                                                </label>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="w-12 md:w-4/12">
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
                        <div class="flex justify-end px-4">
                            <button type="submit" :disabled="isSavingSms" class="inline-flex gap-3 font-title font-semibold rounded px-4 py-3 text-sm justify-center items-center leading-none no-underline border border-solid border-primary-500 text-white bg-primary-500 disabled:opacity-50">
                                <svg x-show="send_at.length" x-cloak xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 448 512"><path d="M128 0c17.7 0 32 14.3 32 32V64H288V32c0-17.7 14.3-32 32-32s32 14.3 32 32V64h48c26.5 0 48 21.5 48 48v48H0V112C0 85.5 21.5 64 48 64H96V32c0-17.7 14.3-32 32-32zM0 192H448V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V192zm64 80v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zm128 0v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H208c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H336zM64 400v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H208zm112 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H336c-8.8 0-16 7.2-16 16z"/></svg>
                                <svg x-show="!send_at.length" xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 512 512"><path d="M498.1 5.6c10.1 7 15.4 19.1 13.5 31.2l-64 416c-1.5 9.7-7.4 18.2-16 23s-18.9 5.4-28 1.6L284 427.7l-68.5 74.1c-8.9 9.7-22.9 12.9-35.2 8.1S160 493.2 160 480V396.4c0-4 1.5-7.8 4.2-10.7L331.8 202.8c5.8-6.3 5.6-16-.4-22s-15.7-6.4-22-.7L106 360.8 17.7 316.6C7.1 311.3 .3 300.7 0 288.9s5.9-22.8 16.1-28.7l448-256c10.7-6.1 23.9-5.5 34 1.4z"/></svg>

                                <span x-show="isSavingSms">SAVING...</span>
                                <span x-show="!isSavingSms"><span x-show="send_at.length" x-cloak>SCHEDULE</span><span x-show="!send_at.length">SEND NOW</span></span>
                            </button>
                        </div>
                        <span x-show="showSmsEditExitForm" x-cloak x-transition.opacity class="fixed top-0 right-0 bottom-0 left-0 bg-black/50"></span>
                        <div :style="showSmsEditExitForm && {'zIndex' : 1057}" class="absolute top-0 right-0 max-w-72">
                            <div x-cloak x-show="showSmsEditExitForm" class="px-3 pr-5 py-2 rounded shadow-lg border border-solid border-gray-400 bg-white">
                                <div class="mb-3 text-sm text-gray-500">Are you sure you want to exit? Your SMS will not be saved, this cannot be undone.</div>
                                <div class="flex gap-2 mb-1">
                                    <button @click="showSmsEditExitForm = false" type="button" class="inline-flex gap-2 font-semibold rounded px-3 py-2 text-sm justify-center items-center leading-none border border-solid border-primary-500 text-white bg-primary-500 disabled:opacity-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-3" fill="currentColor" viewBox="0 0 512 512"><path d="M463.5 224H472c13.3 0 24-10.7 24-24V72c0-9.7-5.8-18.5-14.8-22.2s-19.3-1.7-26.2 5.2L413.4 96.6c-87.6-86.5-228.7-86.2-315.8 1c-87.5 87.5-87.5 229.3 0 316.8s229.3 87.5 316.8 0c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0c-62.5 62.5-163.8 62.5-226.3 0s-62.5-163.8 0-226.3c62.2-62.2 162.7-62.5 225.3-1L327 183c-6.9 6.9-8.9 17.2-5.2 26.2s12.5 14.8 22.2 14.8H463.5z"/></svg>
                                        <span>GO BACK</span>
                                    </button>
                                    <button @click="handleExitSmsForm()" type="button" class="inline-flex gap-2 text-sm font-semibold rounded px-4 py-2 justify-center items-center leading-none border border-solid text-white border-gray-400 bg-gray-400">
                                        <span>YES, EXIT</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 512 512"><path d="M352 96l64 0c17.7 0 32 14.3 32 32l0 256c0 17.7-14.3 32-32 32l-64 0c-17.7 0-32 14.3-32 32s14.3 32 32 32l64 0c53 0 96-43 96-96l0-256c0-53-43-96-96-96l-64 0c-17.7 0-32 14.3-32 32s14.3 32 32 32zm-9.4 182.6c12.5-12.5 12.5-32.8 0-45.3l-128-128c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L242.7 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l210.7 0-73.4 73.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l128-128z"/></svg>
                                    </button>
                                </div>
                            </div>
                            <button type="button" title="Close" @click="handleCloseModalBtn()" data-not-bs-dismiss="modal" aria-label="Close" class="absolute -top-2 -right-1 w-7 h-7 px-0 py-0 border text-gray-500 border-solid border-gray-400 inline-flex items-center justify-center rounded-full bg-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="w-5 h-5" fill="currentColor" viewBox="0 -960 960 960">
                                    <path d="M480-424 284-228q-11 11-28 11t-28-11q-11-11-11-28t11-28l196-196-196-196q-11-11-11-28t11-28q11-11 28-11t28 11l196 196 196-196q11-11 28-11t28 11q11 11 11 28t-11 28L536-480l196 196q11 11 11 28t-11 28q-11 11-28 11t-28-11L480-424Z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- modal end -->


        <!-- sms created modal start -->
        <div class="modal fade" id="smsCreatedModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="smsCreatedModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content !bg-primary-500">
                    <div class="modal-body text-center">
                        <div class="my-3">
                            <span x-show="scheduled" class="inline-flex justify-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#ffffff" viewBox="0 0 448 512"><path d="M128 0c17.7 0 32 14.3 32 32V64H288V32c0-17.7 14.3-32 32-32s32 14.3 32 32V64h48c26.5 0 48 21.5 48 48v48H0V112C0 85.5 21.5 64 48 64H96V32c0-17.7 14.3-32 32-32zM0 192H448V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V192zm64 80v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zm128 0v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H208c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H336zM64 400v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H208zm112 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H336c-8.8 0-16 7.2-16 16z"/></svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#ffffff" viewBox="0 0 512 512"><path d="M256 0a256 256 0 1 1 0 512A256 256 0 1 1 256 0zM232 120V256c0 8 4 15.5 10.7 20l96 64c11 7.4 25.9 4.4 33.3-6.7s4.4-25.9-6.7-33.3L280 243.2V120c0-13.3-10.7-24-24-24s-24 10.7-24 24z"/></svg>
                            </span>
                            <span x-show="!scheduled">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#ffffff" viewBox="0 0 512 512"><path d="M498.1 5.6c10.1 7 15.4 19.1 13.5 31.2l-64 416c-1.5 9.7-7.4 18.2-16 23s-18.9 5.4-28 1.6L284 427.7l-68.5 74.1c-8.9 9.7-22.9 12.9-35.2 8.1S160 493.2 160 480V396.4c0-4 1.5-7.8 4.2-10.7L331.8 202.8c5.8-6.3 5.6-16-.4-22s-15.7-6.4-22-.7L106 360.8 17.7 316.6C7.1 311.3 .3 300.7 0 288.9s5.9-22.8 16.1-28.7l448-256c10.7-6.1 23.9-5.5 34 1.4z"/></svg>
                            </span>
                        </div>

                        <p class="mb-0 text-white" x-text="resMessage"></p>
                        <p class="mb-3 text-white" x-text="resMessage2"></p>
                        <button data-bs-dismiss="modal" type="button" class="border-0 bg-transparent font-semibold text-white inline-flex gap-1 items-center justify-center">
                            <span>VIEW ACTIVITY</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/>
                            </svg>
                        </button>
                        <button type="button" title="Close" data-bs-dismiss="modal" aria-label="Close" class="absolute -top-2 -right-1 w-7 h-7 px-0 py-0 border text-gray-500 border-solid border-gray-400 inline-flex items-center justify-center rounded-full bg-white">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="w-5 h-5" fill="currentColor" viewBox="0 -960 960 960">
                                <path d="M480-424 284-228q-11 11-28 11t-28-11q-11-11-11-28t11-28l196-196-196-196q-11-11-11-28t11-28q11-11 28-11t28 11l196 196 196-196q11-11 28-11t28 11q11 11 11 28t-11 28L536-480l196 196q11 11 11 28t-11 28q-11 11-28 11t-28-11L480-424Z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- sms created modal end -->


        <span x-show="showSmsEditExitForm" x-cloak x-transition.opacity class="fixed top-0 right-0 bottom-0 left-0 bg-black/50"></span>
    </div>
    <x-slot name="headScript">
        <script type="text/javascript">
            var SMS_ROUTE_INDEX = "{{ route('sms.index') }}";
            var TEMPLATES_ROUTE_INDEX = "{{ route('templates.index') }}";
            var CONTACT_GROUPS_ROUTE_INDEX = "{{ route('contact-groups.index') }}";
        </script>
    </x-slot>
</x-admin-layout>
