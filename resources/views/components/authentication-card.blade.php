<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
    <div class="mt-6 mb-6 w-full flex flex-col justify-center items-center">
        <div class="auth-card-box w-full sm:max-w-md bg-white overflow-hidden">
            <div class="px-6 pt-6 pb-3">
                <a href="{{ route('login') }}" class="inline-block mb-3">
                    <img src="/img/logo.png" alt="logo" width="100" height="100" class="h-20 w-auto" />
                </a>
                <h1 class="text-4xl font-extrabold font-title">SMS Portal</h1>
            </div>
            {{ $slot }}
        </div>
        <div class="flex gap-2 items-center my-3 leading-tight text-gray-500">
            <span>powered by</span><img src="/img/sublime-x-black.png" class="h-10" alt="Sublime" />
        </div>
    </div>
</div>
