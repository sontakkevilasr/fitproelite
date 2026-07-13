<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Follow-ups" subtitle="Clients who didn't convert yet — work this list." />
    </x-slot>

    <x-card :padded="false">
        @if($clients->isEmpty())
            <x-empty-state title="No follow-ups due" description="Nice work — your follow-up list is clear." />
        @else
            <div class="divide-y divide-gray-100">
                @foreach($clients as $client)
                    @php $overdue = $client->next_follow_up_at && $client->next_follow_up_at->isPast(); @endphp
                    <a href="{{ route('counsellor.clients.show', $client) }}" class="flex items-center justify-between p-4 hover:bg-gray-50">
                        <div>
                            <p class="font-medium text-gray-900">{{ $client->name }}</p>
                            <p class="text-sm text-gray-500">{{ $client->phone }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm {{ $overdue ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                                {{ $client->next_follow_up_at?->format('d M Y') ?? 'No date set' }}
                            </p>
                            @if($overdue)
                                <p class="text-xs text-red-500">Overdue</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="p-4 border-t border-gray-100">{{ $clients->links() }}</div>
        @endif
    </x-card>
</x-app-layout>
