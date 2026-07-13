<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $trainer->user->name }} - {{ config('app.name', 'Schedulars') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-900">
        <div class="min-h-screen flex items-center justify-center px-4 py-10">
            <div class="w-full max-w-md bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
                @if($trainer->photoUrl())
                    <img src="{{ $trainer->photoUrl() }}" alt="{{ $trainer->user->name }}" class="h-28 w-28 rounded-full object-cover mx-auto">
                @else
                    <span class="flex h-28 w-28 mx-auto items-center justify-center rounded-full bg-primary-100 text-primary-700 text-3xl font-semibold">
                        {{ Str::of($trainer->user->name)->substr(0, 1)->upper() }}
                    </span>
                @endif

                <h1 class="mt-4 text-xl font-semibold text-gray-900">{{ $trainer->user->name }}</h1>
                <p class="text-primary-600 font-medium">{{ $trainer->category->name }}</p>

                @if($trainer->bio)
                    <p class="mt-4 text-sm text-gray-600">{{ $trainer->bio }}</p>
                @endif

                <div class="mt-6 pt-6 border-t border-gray-100 text-xs text-gray-400">
                    {{ config('app.name', 'Schedulars') }} &middot; Trainer Profile
                </div>
            </div>
        </div>
    </body>
</html>
