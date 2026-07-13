<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Select a Trainer" :subtitle="$category->name.' for '.$client->name" />
    </x-slot>

    @if($trainers->isEmpty())
        <x-empty-state title="No trainers in this category" description="Ask an admin to add a trainer to this category." />
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($trainers as $trainer)
                <a href="{{ route('booking.calendar', ['type' => $type, 'client' => $client, 'trainerProfile' => $trainer]) }}">
                    <x-card class="hover:border-primary-300 transition h-full">
                        <div class="flex items-center gap-3">
                            @if($trainer->photoUrl())
                                <img src="{{ $trainer->photoUrl() }}" alt="{{ $trainer->user->name }}" class="h-12 w-12 rounded-full object-cover">
                            @else
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-primary-100 text-primary-700 font-semibold">
                                    {{ Str::of($trainer->user->name)->substr(0, 1)->upper() }}
                                </span>
                            @endif
                            <div>
                                <p class="font-semibold text-gray-900">{{ $trainer->user->name }}</p>
                                <p class="text-sm text-gray-500">{{ Str::limit($trainer->bio, 60) }}</p>
                            </div>
                        </div>
                    </x-card>
                </a>
            @endforeach
        </div>
    @endif
</x-app-layout>
