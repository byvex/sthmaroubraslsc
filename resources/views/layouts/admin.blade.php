@props([
    'aos' => 0,
    'users' > 0,
    'tippy' => 0,
    'swiper' => 0,
    'flags'=> 0,
    'toastify' => 0,
    'autosize' => 0,
    'flatpickr' => 0,
    'sweetalert' => 0,
    'activity' => 0,
    'contacts' => 0,
    'templates' => 0,
    'contactGroups' => 0,
    'title' => config('app.name', 'Laravel'),
    'description' => 'Lyceuma',
])<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

        <!-- Scripts -->
        @vite(['resources/css/app.scss', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles

        @php
        $version = date('Y-m-d-h-i-s');
        $stylesArr = [
            'reboot' => '/css/reboot.css?v=2024-7-10',
            'global' => '/css/global.css?v=' . $version,
            'toastify' => !empty($toastify) ? '/css/lib/toastify.min.css?v=1.12.0' : '',
            'flatpickr' => !empty($flatpickr) ? '/css/lib/flatpickr.min.css?v=4.6.13' : '',
            'flags' => !empty($flags) ? 'https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/7.2.1/css/flag-icons.min.css' : '',
            'inter' => 'https://fonts.googleapis.com/css2?family=Inter:wght@500;600;800&display=swap',
            'wixMadeForDisplay' => 'https://fonts.googleapis.com/css2?family=Wix+Madefor+Display:wght@400;600;700;800&display=swap',
        ];
        @endphp
        @foreach($stylesArr as $stylePath)
            @if( !empty($stylePath) )
                <link rel="preload" as="style" href="{{ $stylePath }}" />
                <link rel="stylesheet" href="{{ $stylePath }}" />
            @endif
        @endforeach

        <script defer src="/js/lib/axios.min.js?v=1.6.8"></script>
        <script defer src="/js/base.js?v={{ $version }}"></script>
        @if(!empty($headScript))
            {{ $headScript }}
        @endif

        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
        <link rel="manifest" href="/site.webmanifest">
    </head>
    <body class="antialiased bg-gray-100">
        <x-banner />
        @livewire('navigation-menu')
        <main>
            {{ $slot }}
        </main>
        @stack('modals')

        @php
        $scriptsArr = [
            'toastify' => !empty($toastify) ? '/js/lib/toastify.min.js?v=1.12.0' : '',
            'sweetalert' => !empty($sweetalert) ? '/js/lib/sweetalert2.min.js?v=11.9.0' : '',
            'popper' => !empty($tippy) ? '/js/lib/popper.min.js?v=2.11.8' : '',
            'autosize' => !empty($autosize) ? '/js/lib/autosize.min.js?v=6.0.1' : '',
            'flatpickr' => !empty($flatpickr) ? '/js/lib/flatpickr.min.js?v=4.6.13' : '',
            'tippy' => !empty($tippy) ? '/js/lib/tippy-bundle.umd.min.js?v=6.3.7' : '',
            'global' => '/js/global.js?v='. $version,
            'users' => !empty($users) ? '/js/users.js?v='. $version : '',
            'activity' => !empty($activity) ? '/js/activity.js?v='. $version : '',
            'contacts' => !empty($contacts) ? '/js/contacts.js?v='. $version : '',
            'templates' => !empty($templates) ? '/js/templates.js?v='. $version : '',
            'contactGroups' => !empty($contactGroups) ? '/js/contact-groups.js?v='. $version : '',
        ];
        @endphp

        <script type="text/javascript">
            var USERS_PROFILE_UPDATE_INDEX = "{{ route('users.profile.update') }}";
        </script>

        @foreach($scriptsArr as $scriptPath)
            @if( !empty($scriptPath) )
                <script defer src="{{ $scriptPath }}"></script>
            @endif
        @endforeach

        <script src="/js/web-push.js?v=1.1"></script>

        @livewireScripts
    </body>
</html>
