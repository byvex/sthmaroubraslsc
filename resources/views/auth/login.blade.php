<x-app-layout>
    <x-authentication-card>
        {{-- <div class="mb-4"><x-button.google /></div> --}}
        <form method="POST" action="{{ route('login') }}" class="block">
            <div class="px-6">
                <h2 class="text-lg text-gray-600 mb-3">Login to your account</h2>
                <x-validation-errors class="mb-4" />
                @session('status')
                    <div class="mb-4 font-medium text-sm text-green-600">
                        {{ $value }}
                    </div>
                @endsession

                @csrf

                <div>
                    <x-label for="email" value="{{ __('Username or Email') }}" />
                    <x-input id="email" class="block mt-1 w-full" type="text" name="email" :value="old('email')" required autofocus autocomplete="username" />
                </div>

                <div class="mt-4 mb-4">
                    <x-label for="password" value="{{ __('Password') }}" />
                    <div x-data="{show: false}" class="relative">
                        <input id="password" :type="show ? 'text' : 'password'" type="password" name="password" required autocomplete="current-password" class="block mt-1 w-full border-gray-300 focus:border-primary-500 focus:ring-primary-400 rounded-md shadow-sm" />
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
                    @if (Route::has('password.request'))
                        <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-400" href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif
                </div>

                {{--
                <div class="block mt-4">
                    <label for="remember_me" class="flex items-center">
                        <x-checkbox id="remember_me" name="remember" />
                        <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                    </label>
                </div>
                 --}}

            </div>
            <div class="">
                <button type="submit" class="auth-card-login-btn uppercase font-bold border-0 w-full text-white">
                    {{ __('Log in') }}
                </button>
            </div>
        </form>
    </x-authentication-card>
</x-app-layout>
