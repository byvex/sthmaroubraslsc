@props([
    'active'=> false,
    'href'=> '#!',
])
<a href="{{ $href }}" class="{{ !empty($active) ? 'font-extrabold text-white bg-black hover:bg-gray-900' : 'font-semibold text-black hover:bg-gray-200' }} inline-flex font-title rounded min-w-32 min-h-11 justify-center items-center leading-none no-underline border border-solid border-black">
    <span>{{ $slot }}</span>
</a>