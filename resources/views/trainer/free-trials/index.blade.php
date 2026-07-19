<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Free Trials" subtitle="Every free trial you've booked." />
    </x-slot>

    @if(session('status'))
        <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3">{{ session('status') }}</div>
    @endif

    <x-card :padded="false">
        @if($trials->isEmpty())
            <x-empty-state title="No free trials booked yet" description="Free trials you book after an assessment will show up here." />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Client</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Sessions</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Trainer(s)</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Categories</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($trials as $trial)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $trial->client->name }}</td>
                                <td class="px-4 py-3">{{ $trial->sessions->count() }} of {{ $trial->total_sessions }}</td>
                                <td class="px-4 py-3">{{ $trial->sessions->pluck('trainerProfile.user.name')->unique()->join(', ') }}</td>
                                <td class="px-4 py-3">{{ $trial->sessions->pluck('category.name')->unique()->join(', ') }}</td>
                                <td class="px-4 py-3"><x-status-badge :status="$trial->status" /></td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('trainer.free-trials.show', $trial) }}" class="text-primary-600 hover:text-primary-800 font-medium">View</a>
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
