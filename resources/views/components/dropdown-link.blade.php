@props(['active' => false])

@php
$classes = ($active ?? false)
    ? 'block w-full px-4 py-2 text-start text-sm leading-5 text-white bg-white/10 focus:outline-none transition duration-150 ease-in-out'
    : 'block w-full px-4 py-2 text-start text-sm leading-5 text-slate-200 hover:bg-white/10 hover:text-white focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>