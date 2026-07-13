<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Admin Dashboard" subtitle="Studio-wide overview" />
    </x-slot>

    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <x-stat-card label="Trainers" :value="$totalTrainers" />
        <x-stat-card label="Counsellors" :value="$totalCounsellors" />
        <x-stat-card label="Clients" :value="$totalClients" />
        <x-stat-card label="Trials Scheduled" :value="$trialsScheduled" />
        <x-stat-card label="Converted" :value="$converted" />
        <x-stat-card label="Awaiting Follow-up" :value="$followUps" />
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('admin.users.index') }}" class="block">
            <x-card class="hover:border-primary-300 transition">
                <p class="font-semibold text-gray-900">Users</p>
                <p class="text-sm text-gray-500 mt-1">Manage logins for admins, counsellors &amp; trainers.</p>
            </x-card>
        </a>
        <a href="{{ route('admin.trainer-categories.index') }}" class="block">
            <x-card class="hover:border-primary-300 transition">
                <p class="font-semibold text-gray-900">Trainer Categories</p>
                <p class="text-sm text-gray-500 mt-1">Functional, Yoga, Aerobics, Assessment...</p>
            </x-card>
        </a>
        <a href="{{ route('admin.packages.index') }}" class="block">
            <x-card class="hover:border-primary-300 transition">
                <p class="font-semibold text-gray-900">Packages</p>
                <p class="text-sm text-gray-500 mt-1">Membership packages clients can be interested in.</p>
            </x-card>
        </a>
        <a href="{{ route('admin.trainers.index') }}" class="block">
            <x-card class="hover:border-primary-300 transition">
                <p class="font-semibold text-gray-900">Trainers</p>
                <p class="text-sm text-gray-500 mt-1">Browse trainer profiles &amp; availability.</p>
            </x-card>
        </a>
        <a href="{{ route('admin.calendar.index') }}" class="block">
            <x-card class="hover:border-primary-300 transition">
                <p class="font-semibold text-gray-900">Master Calendar</p>
                <p class="text-sm text-gray-500 mt-1">See any trainer's booked and free slots.</p>
            </x-card>
        </a>
        <a href="{{ route('admin.trials.index') }}" class="block">
            <x-card class="hover:border-primary-300 transition">
                <p class="font-semibold text-gray-900">Trials</p>
                <p class="text-sm text-gray-500 mt-1">Every pre-trial visit and free trial booked.</p>
            </x-card>
        </a>
        <a href="{{ route('admin.notifications.index') }}" class="block">
            <x-card class="hover:border-primary-300 transition">
                <p class="font-semibold text-gray-900">Notifications</p>
                <p class="text-sm text-gray-500 mt-1">WhatsApp messages logged for every booking.</p>
            </x-card>
        </a>
        <a href="{{ route('admin.reports.index') }}" class="block">
            <x-card class="hover:border-primary-300 transition">
                <p class="font-semibold text-gray-900">Reports</p>
                <p class="text-sm text-gray-500 mt-1">Conversion rate and trial activity.</p>
            </x-card>
        </a>
    </div>
</x-app-layout>
