<x-app-layout>
    <x-slot name="header">
        <x-page-header title="All Clients" subtitle="Every lead and member across all counsellors." />
    </x-slot>

    <x-card :padded="false">
        <form method="GET" class="flex flex-wrap gap-3 p-4 border-b border-gray-200">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or phone"
                   class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm flex-1 min-w-[200px]">
            <select name="status" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm">
                <option value="">All statuses</option>
                @foreach(['new','pre_visit_scheduled','assessment_completed','trial_scheduled','converted','follow_up','lost'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucwords(str_replace('_',' ',$status)) }}</option>
                @endforeach
            </select>
            <x-secondary-button type="submit">Filter</x-secondary-button>
        </form>

        @if($clients->isEmpty())
            <x-empty-state title="No clients found" />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Name</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Counsellor</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Package</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($clients as $client)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $client->name }}</div>
                                    <div class="text-gray-500">{{ $client->phone }}</div>
                                </td>
                                <td class="px-4 py-3">{{ $client->counsellor->name }}</td>
                                <td class="px-4 py-3">{{ $client->package?->name ?? '—' }}</td>
                                <td class="px-4 py-3"><x-status-badge :status="$client->status" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100">{{ $clients->links() }}</div>
        @endif
    </x-card>
</x-app-layout>
