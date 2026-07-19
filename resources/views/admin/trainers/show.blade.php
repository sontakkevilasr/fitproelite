<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="$trainer->user->name" :subtitle="$trainer->categories->pluck('name')->join(', ') ?: '—'">
            <x-slot name="actions">
                <a href="{{ route('admin.users.edit', $trainer->user) }}">
                    <x-secondary-button>Edit User</x-secondary-button>
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <x-card class="lg:col-span-1">
            <div class="flex flex-col items-center text-center">
                @if($trainer->photoUrl())
                    <img src="{{ $trainer->photoUrl() }}" alt="{{ $trainer->user->name }}" class="h-24 w-24 rounded-full object-cover">
                @else
                    <span class="flex h-24 w-24 items-center justify-center rounded-full bg-primary-100 text-primary-700 text-2xl font-semibold">
                        {{ Str::of($trainer->user->name)->substr(0, 1)->upper() }}
                    </span>
                @endif
                <p class="mt-3 font-semibold text-gray-900">{{ $trainer->user->name }}</p>
                <p class="text-sm text-gray-500">{{ $trainer->user->email }}</p>
                <p class="text-sm text-gray-500">{{ $trainer->phone ?? $trainer->user->phone }}</p>
                <p class="mt-3 text-sm text-gray-600">{{ $trainer->bio }}</p>
            </div>
        </x-card>

        <x-card class="lg:col-span-2">
            <h3 class="font-semibold text-gray-900 mb-3">Weekly Availability</h3>
            @if($trainer->weeklySlots->isEmpty())
                <x-empty-state title="No weekly availability set" />
            @else
                @php
                    $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                    $byDay = $trainer->weeklySlots->groupBy('day_of_week')->sortKeys();
                @endphp
                <div class="space-y-2">
                    @foreach($byDay as $day => $slots)
                        <div class="flex items-start gap-3 text-sm">
                            <span class="w-12 font-medium text-gray-700">{{ $days[$day] }}</span>
                            <span class="text-gray-500">
                                {{ $slots->map(fn($s) => \Carbon\Carbon::parse($s->start_time)->format('g:i A').' – '.\Carbon\Carbon::parse($s->end_time)->format('g:i A'))->join(', ') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif

            <h3 class="font-semibold text-gray-900 mt-6 mb-3">Blocked Dates</h3>
            @if($trainer->blockedSlots->isEmpty())
                <p class="text-sm text-gray-500">No blocked dates.</p>
            @else
                <ul class="space-y-1 text-sm text-gray-600">
                    @foreach($trainer->blockedSlots as $block)
                        <li>{{ $block->block_date->format('d M Y') }} — {{ $block->reason ?? 'Blocked' }}</li>
                    @endforeach
                </ul>
            @endif
        </x-card>
    </div>
</x-app-layout>
