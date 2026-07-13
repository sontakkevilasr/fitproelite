@props(['label'])

<div class="pt-4 first:pt-0">
    <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ $label }}</p>
    <div class="space-y-1">
        {{ $slot }}
    </div>
</div>
