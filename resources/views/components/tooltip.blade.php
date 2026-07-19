@props(['text'])

<span class="relative inline-block group">
    {{ $slot }}
    @if($text)
        <span class="pointer-events-none absolute z-20 hidden group-hover:block bottom-full left-0 mb-1.5 w-max max-w-xs px-2.5 py-1.5 text-xs leading-snug text-white bg-gray-800 rounded-md shadow-lg whitespace-normal">
            {{ $text }}
            <span class="absolute top-full left-3 border-4 border-transparent border-t-gray-800"></span>
        </span>
    @endif
</span>
