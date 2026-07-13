@props(['href', 'active' => false])

@php
$classes = $active
    ? 'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-semibold bg-primary-50 text-primary-700'
    : 'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900';
@endphp

<a href="{{ $href }}" class="{{ $classes }}">
    {{ $slot }}
</a>
