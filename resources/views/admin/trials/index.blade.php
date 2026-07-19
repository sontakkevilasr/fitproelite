<x-app-layout>
    <x-slot name="header">
        <x-page-header title="All Trials" subtitle="Pre-trial visits and free trials across the studio." />
    </x-slot>

    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <select name="type" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
            <option value="">All types</option>
            <option value="pre_visit" @selected(request('type') === 'pre_visit')>Pre-Trial Visit</option>
            <option value="free_trial" @selected(request('type') === 'free_trial')>Free Trial</option>
        </select>
        <select name="status" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach(['scheduled','completed','converted','lost','cancelled'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </form>

    <x-card :padded="false">
        @if($trials->isEmpty())
            <x-empty-state title="No trials found" />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Client</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Type</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Trainer</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Counsellor</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($trials as $trial)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $trial->client->name }}</td>
                                <td class="px-4 py-3">{{ $trial->type === 'pre_visit' ? 'Pre-Trial Visit' : 'Free Trial' }}</td>
                                <td class="px-4 py-3">{{ $trial->sessions->pluck('trainerProfile.user.name')->unique()->join(', ') }}</td>
                                <td class="px-4 py-3">{{ $trial->counsellor->name }}</td>
                                <td class="px-4 py-3"><x-status-badge :status="$trial->status" /></td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.trials.show', $trial) }}" class="text-primary-600 hover:text-primary-800 font-medium">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100">{{ $trials->links() }}</div>
        @endif
    </x-card>
</x-app-layout>
