@props(['padded' => true])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-sm border border-gray-200 '.($padded ? 'p-6' : '')]) }}>
    {{ $slot }}
</div>
