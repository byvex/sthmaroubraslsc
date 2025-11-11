@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 focus:border-primary-500 focus:ring-primary-400 rounded-md shadow-sm']) !!}>
