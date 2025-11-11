<x-admin-layout toastify="1">
    <div class="max-w-screen-xl mx-auto px-4 my-4">
        <div class="mb-3">
            <h4 class="font-bold">Note</h4>
            <ol>
                <li>Super Admins can edit all users</li>
                <li>Admins can edit only childrens</li>
                <li>Users can edit only their profile</li>
            </ol>
        </div>
        <form action="{{ route('users.store') }}" method="post" data-js="app-form">
            <div class="flex flex-wrap -mx-1">
                <div class="w-full sm:w-6/12 px-1 mb-2">
                    <div class="flex flex-col">
                        <span>Name*</span>
                        <input type="text" name="name" value="" required class="rounded focus:border-primary-500 focus:ring-primary-400" />
                    </div>
                </div>
                <div class="w-full sm:w-6/12 px-1 mb-2">
                    <div class="flex flex-col">
                        <span>Surname</span>
                        <input type="text" name="lastname" value="" class="rounded focus:border-primary-500 focus:ring-primary-400" />
                    </div>
                </div>
                <div class="w-full sm:w-6/12 px-1 mb-2">
                    <div class="flex flex-col">
                        <span>Company Name*</span>
                        <input type="text" name="company" value="" class="rounded focus:border-primary-500 focus:ring-primary-400" />
                    </div>
                </div>
                <div class="w-full sm:w-6/12 px-1 mb-2">
                    <div class="flex flex-col">
                        <span>Username*</span>
                        <input type="text" name="username" value="" class="rounded focus:border-primary-500 focus:ring-primary-400" />
                    </div>
                </div>
                <div class="w-full sm:w-6/12 px-1 mb-2">
                    <div class="flex flex-col">
                        <span>Email*</span>
                        <input type="email" name="email" value="" required autocomplete="off" class="rounded focus:border-primary-500 focus:ring-primary-400" />
                    </div>
                </div>
                <div class="w-full sm:w-6/12 px-1 mb-2">
                    <div class="flex flex-col">
                        <span>Phone</span>
                        <input type="number" name="phone" value="" maxlength="10" minlength="10" class="rounded focus:border-primary-500 focus:ring-primary-400" />
                    </div>
                </div>
                @if( !empty($user_roles) && !$current_user->isUser() )
                <div class="w-full sm:w-6/12 px-1 mb-2">
                    <div class="flex flex-col">
                        <span>Role</span>
                        <select name="role" class="rounded focus:border-primary-500 focus:ring-primary-400">
                            @foreach($user_roles as $user_role)
                                @if($current_user->isAdmin())
                                    @if($user_role == 'SUPERADMIN')
                                        @continue
                                    @endif
                                @endif
                                <option value="{{ $user_role }}">{{ $user_role }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif
                <div class="w-full sm:w-6/12 px-1 mb-2">
                    <div class="flex flex-col">
                        <span>Password</span>
                        <div x-data="{show: false}" class="relative">
                            <input :type="show ? 'text' : 'password'" name="password" value="" autocomplete="off" required class="w-full rounded focus:border-primary-500 focus:ring-primary-400" />
                            <button x-cloak @click="show = !show" :title="show ? 'Hide Password':'Show Password'" type="button" class="absolute top-0 bottom-0 right-0 inline-flex items-center justify-center mx-1 my-1 px-2 py-1 rounded-md border bg-gray-100 text-gray-700 focus:outline-primary-500">
                                <span :class="show ? 'inline-block' : 'hidden'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="h-5 w-5 bi bi-eye-slash" viewBox="0 0 16 16">
                                      <path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-2.79.588l.77.771A6 6 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755q-.247.248-.517.486z"/>
                                      <path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829"/>
                                      <path d="M3.35 5.47q-.27.24-.518.487A13 13 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7 7 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12z"/>
                                    </svg>
                                </span>
                                <span :class="show ? 'hidden' : 'inline-block'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="h-5 w-5 bi bi-eye" viewBox="0 0 16 16">
                                      <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                                      <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="w-full px-1">
                    <div data-js="app-form-status" class="hidden font-semibold hidden w-full mb-2"></div>
                    <x-button type="submit" data-js="app-form-btn">Create User</x-button>
                </div>
            </div>
        </form>
        <br><br><br><br><br>
    </div>
</x-admin-layout>