<x-app-layout>
    <x-slot name="header">
        <x-page-header title="My Clients" subtitle="Leads and members you've created.">
            <x-slot name="actions">
                <a href="{{ route('counsellor.lookup.index') }}">
                    <x-primary-button>+ New Enquiry</x-primary-button>
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-card :padded="false">
        <form method="GET" class="p-4 border-b border-gray-200">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or phone"
                   class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm w-full sm:w-72">
        </form>

        @if($clients->isEmpty())
            <x-empty-state title="No clients yet" description="Start by capturing a new enquiry." />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Name</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Package</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Interest</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($clients as $client)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $client->name }}</div>
                                    <div class="text-gray-500">{{ $client->phone }}</div>
                                </td>
                                <td class="px-4 py-3">{{ $client->package?->name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @if($client->interestLevel)
                                        <span class="inline-flex items-center gap-1.5">
                                            <span class="h-2 w-2 rounded-full" style="background-color: {{ $client->interestLevel->color }}"></span>
                                            {{ $client->interestLevel->name }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3"><x-status-badge :status="$client->status" /></td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('counsellor.clients.show', $client) }}" class="text-primary-600 hover:text-primary-800 font-medium">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100">{{ $clients->links() }}</div>
        @endif
    </x-card>
</x-app-layout>
