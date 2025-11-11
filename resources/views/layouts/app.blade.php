<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Scripts -->
        @vite(['resources/css/app.scss', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles

        @php
        $version = date('Y-m-d-h-i-s');
        $stylesArr = [
            'reboot' => '/css/reboot.css?v=2024-7-10',
            'global' => '/css/global.css?v=' . $version,
            'auth' => '/css/auth.css?v=' . $version,
            'toastify' => '/css/lib/toastify.min.css?v=1.12.0',
            'inter' => 'https://fonts.googleapis.com/css2?family=Inter:wght@500;600;800&display=swap',
            'widMadeForDisplay' => 'https://fonts.googleapis.com/css2?family=Wix+Madefor+Display:wght@400;600;700;800&display=swap',
        ];
        @endphp
        @foreach($stylesArr as $stylePath)
            @if( !empty($stylePath) )
                <link rel="preload" as="style" href="{{ $stylePath }}" />
                <link rel="stylesheet" href="{{ $stylePath }}" />
            @endif
        @endforeach

        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
        <link rel="manifest" href="/site.webmanifest">
    </head>
    <body class="antialiased">
        <x-banner />
        <main>
            {{ $slot }}
        </main>
        @stack('modals')

        <script src="/js/web-push.js?v=1.1"></script>

        @livewireScripts
    </body>
</html>
