<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Trainer Dashboard" subtitle="{{ 'Welcome back, '.auth()->user()->name }}" />
    </x-slot>

    @if(!$trainerProfile)
        <x-empty-state title="No trainer profile set up" description="Ask an admin to link your account to a trainer category." />
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-stat-card label="Today's Sessions" :value="$todaySessions" />
            <x-stat-card label="Upcoming Sessions" :value="$upcomingSessions" />
        </div>
    @endif
</x-app-layout>
