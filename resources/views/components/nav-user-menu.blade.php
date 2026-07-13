@props(['direction' => 'down'])

<x-dropdown align="right" width="56" :direction="$direction">
    <x-slot name="trigger">
        <button class="flex items-center gap-2 w-full rounded-md px-2 py-1.5 text-sm hover:bg-gray-100 focus:outline-none">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-100 text-primary-700 font-semibold">
                {{ Str::of(Auth::user()->name)->substr(0, 1)->upper() }}
            </span>
            <span class="hidden lg:flex flex-col items-start min-w-0">
                <span class="truncate text-sm font-medium text-gray-800">{{ Auth::user()->name }}</span>
                <span class="truncate text-xs text-gray-500">{{ ucfirst(Auth::user()->getRoleNames()->first() ?? '') }}</span>
            </span>
            <svg class="hidden lg:block ml-auto h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </x-slot>

    <x-slot name="content">
        <div class="px-4 py-2 border-b border-gray-100 lg:hidden">
            <div class="text-sm font-medium text-gray-800">{{ Auth::user()->name }}</div>
            <div class="text-xs text-gray-500">{{ Auth::user()->email }}</div>
        </div>

        <x-dropdown-link :href="route('profile.edit')">
            {{ __('Profile') }}
        </x-dropdown-link>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-dropdown-link :href="route('logout')"
                    onclick="event.preventDefault(); this.closest('form').submit();">
                {{ __('Log Out') }}
            </x-dropdown-link>
        </form>
    </x-slot>
</x-dropdown>
