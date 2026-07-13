<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Book a Free Trial" :subtitle="'For '.$client->name" />
    </x-slot>

    @if($categories->isEmpty())
        <x-empty-state title="No trial categories configured" description="Ask an admin to add trainer categories." />
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($categories as $category)
                <a href="{{ route('booking.trainers', ['type' => $type, 'client' => $client, 'category_id' => $category->id]) }}">
                    <x-card class="hover:border-primary-300 transition h-full relative">
                        @if($recommendedId === $category->id)
                            <span class="absolute top-3 right-3 text-xs font-medium bg-primary-100 text-primary-700 px-2 py-0.5 rounded-full">Recommended</span>
                        @endif
                        <p class="font-semibold text-gray-900">{{ $category->name }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ Str::limit($category->description, 80) }}</p>
                    </x-card>
                </a>
            @endforeach
        </div>
    @endif
</x-app-layout>
