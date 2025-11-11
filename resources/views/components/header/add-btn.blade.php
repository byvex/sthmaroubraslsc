@props([ 'href'=> '', 'type' => '', ])
@php
    $classes = 'inline-flex font-title px-2 gap-2 items-center no-underline focus:outline-none border-0 bg-transparent font-semibold text-primary-500';
@endphp
<{{ !empty($href) ? 'a' : 'button' }} {{ !empty($href) ? 'href='. $href . '' : '' }} {{ !empty($href) ? '' : ( !empty($type) ? 'type="'. $type .'"' : 'type="button"' ) }} {{ $attributes->merge(['class' => $classes ]) }}>
    <span class="w-9 h-9 inline-flex items-center justify-center rounded-full bg-primary-500">
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none"><path fill="#fff" fill-rule="evenodd" d="M7.2 0H4.8v4.8H0v2.4h4.8V12h2.4V7.2H12V4.8H7.2V0Z" clip-rule="evenodd"/></svg>
    </span>
    <span>{{ $slot }}</span>
</{{ !empty($href) ? 'a' : 'button' }}>