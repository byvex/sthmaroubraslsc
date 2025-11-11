<x-app-layout>
    <x-authentication-card>
        <form method="POST" action="{{ route('password.update') }}">
            <div class="px-6">
                @csrf
                <x-validation-errors class="mb-4" />

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="block">
                    <x-label for="email" value="{{ __('Email') }}" />
                    <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                </div>

                <div class="mt-4">
                    <x-label for="password" value="{{ __('Password') }}" />
                    <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                </div>

                <div class="mt-4 mb-4">
                    <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                    <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                </div>
            </div>
            <div class="">
                <button type="submit" class="auth-card-login-btn uppercase font-bold border-0 w-full text-white">
                    {{ __('Reset Password') }}
                </button>
            </div>
        </form>
    </x-authentication-card>
</x-app-layout>
