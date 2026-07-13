<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name', 'Schedulars') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-900">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen">

            {{-- Mobile top bar --}}
            <div class="lg:hidden sticky top-0 z-30 flex items-center justify-between gap-3 bg-white border-b border-gray-200 px-4 py-3">
                <button @click="sidebarOpen = true" class="p-2 -ml-2 text-gray-600 hover:text-primary-600 rounded-md">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-semibold text-primary-700">
                    <x-application-logo class="h-7 w-7 fill-current text-primary-600" />
                    <span>{{ config('app.name', 'Schedulars') }}</span>
                </a>
                <x-nav-user-menu />
            </div>

            {{-- Mobile sidebar overlay --}}
            <div x-show="sidebarOpen" x-cloak
                 class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden"
                 @click="sidebarOpen = false"
                 x-transition:enter="transition-opacity ease-linear duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"></div>

            {{-- Sidebar (desktop fixed / mobile off-canvas) --}}
            <aside
                x-show="sidebarOpen || window.innerWidth >= 1024"
                x-cloak
                @click.outside="sidebarOpen = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="fixed inset-y-0 left-0 z-50 w-sidebar bg-white border-r border-gray-200 flex flex-col lg:translate-x-0"
            >
                <div class="hidden lg:flex items-center gap-2 px-6 h-16 border-b border-gray-200 font-semibold text-primary-700 text-lg">
                    <x-application-logo class="h-8 w-8 fill-current text-primary-600" />
                    {{ config('app.name', 'Schedulars') }}
                </div>

                <div class="flex items-center justify-between px-4 h-16 border-b border-gray-200 lg:hidden">
                    <span class="font-semibold text-primary-700">{{ config('app.name', 'Schedulars') }}</span>
                    <button @click="sidebarOpen = false" class="p-2 text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
                    @include('layouts.navigation')
                </nav>

                <div class="hidden lg:block border-t border-gray-200 p-3">
                    <x-nav-user-menu direction="up" />
                </div>
            </aside>

            {{-- Main content --}}
            <div class="lg:pl-sidebar flex flex-col min-h-screen">
                @isset($header)
                    <header class="bg-white border-b border-gray-200">
                        <div class="px-4 sm:px-6 lg:px-8 py-5">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
