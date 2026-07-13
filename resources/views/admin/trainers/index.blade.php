<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Trainers" subtitle="Browse trainer profiles and their categories." />
    </x-slot>

    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <select name="category_id" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
            <option value="">All categories</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </form>

    @if($trainers->isEmpty())
        <x-card>
            <x-empty-state title="No trainers found" description="Create a trainer from the Users page." />
        </x-card>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($trainers as $trainer)
                <a href="{{ route('admin.trainers.show', $trainer) }}">
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
                                <p class="text-sm text-gray-500">{{ $trainer->category->name }}</p>
                            </div>
                        </div>
                        <p class="mt-3 text-sm text-gray-500">{{ Str::limit($trainer->bio, 90) }}</p>
                    </x-card>
                </a>
            @endforeach
        </div>
    @endif
</x-app-layout>
