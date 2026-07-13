<x-app-layout>
    <x-slot name="header">
        <x-page-header title="My Calendar" subtitle="Green = free, red = booked, grey = blocked." />
    </x-slot>

    <x-card :padded="false">
        <div class="p-2">
            <x-calendar :events-url="route('trainer.calendar.events')" />
        </div>
    </x-card>
</x-app-layout>
