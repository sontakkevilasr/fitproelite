<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Master Calendar" subtitle="View any trainer's booked and free slots." />
    </x-slot>

    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <select name="category_id" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
            <option value="">All categories</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <select name="trainer_id" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
            @forelse($trainers as $trainer)
                <option value="{{ $trainer->id }}" @selected(optional($selectedTrainer)->id === $trainer->id)>{{ $trainer->user->name }} ({{ $trainer->categories->pluck('name')->join(', ') ?: '—' }})</option>
            @empty
                <option value="">No trainers</option>
            @endforelse
        </select>
    </form>

    <x-card :padded="false">
        @if(!$selectedTrainer)
            <x-empty-state title="No trainer selected" />
        @else
            <div class="p-2">
                <x-calendar :events-url="route('admin.calendar.events', ['trainer_id' => $selectedTrainer->id])" />
            </div>
        @endif
    </x-card>
</x-app-layout>
