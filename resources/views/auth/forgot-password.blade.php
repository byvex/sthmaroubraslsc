<x-app-layout>
    <x-authentication-card>
        <form method="POST" action="{{ route('password.email') }}">
            <div class="px-6">
                <div class="mb-4 text-sm text-gray-600">
                    {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
                </div>

                @session('status')
                    <div class="mb-4 font-medium text-sm text-green-600">
                        {{ $value }}
                    </div>
                @endsession

                <x-validation-errors class="mb-4" />
                @csrf

                <div class="block mb-4">
                    <x-label for="email" value="{{ __('Email') }}" />
                    <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                </div>
            </div>
            <div class="">
                <button type="submit" class="auth-card-login-btn uppercase font-bold border-0 w-full text-white">
                    {{ __('Email Password Reset Link') }}
                </button>
            </div>
        </form>
    </x-authentication-card>
</x-app-layout>
