<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Blocked Dates" subtitle="Mark leave or unavailable time so it won't be offered for booking." />
    </x-slot>

    @if(session('status'))
        <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3">{{ session('status') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <x-card>
            <h3 class="font-semibold text-gray-900 mb-3">Add Blocked Date</h3>
            <form method="POST" action="{{ route('trainer.blocked-slots.store') }}" class="space-y-4">
                @csrf
                <div>
                    <x-input-label for="block_date" value="Date" />
                    <x-text-input id="block_date" type="date" name="block_date" class="mt-1" required />
                    <x-input-error :messages="$errors->get('block_date')" class="mt-2" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-input-label for="start_time" value="From (optional)" />
                        <x-text-input id="start_time" type="time" name="start_time" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="end_time" value="To (optional)" />
                        <x-text-input id="end_time" type="time" name="end_time" class="mt-1" />
                    </div>
                </div>
                <p class="text-xs text-gray-400">Leave both times blank to block the whole day.</p>
                <div>
                    <x-input-label for="reason" value="Reason (optional)" />
                    <x-text-input id="reason" name="reason" class="mt-1" />
                    <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                </div>
                <x-primary-button>Add Block</x-primary-button>
            </form>
        </x-card>

        <x-card class="lg:col-span-2" :padded="false">
            @if($blockedSlots->isEmpty())
                <x-empty-state title="No blocked dates" />
            @else
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Date</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Time</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Reason</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($blockedSlots as $block)
                            <tr>
                                <td class="px-4 py-3">{{ $block->block_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-gray-500">
                                    {{ $block->start_time ? \Carbon\Carbon::parse($block->start_time)->format('g:i A').' - '.\Carbon\Carbon::parse($block->end_time)->format('g:i A') : 'Whole day' }}
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $block->reason ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <form method="POST" action="{{ route('trainer.blocked-slots.destroy', $block) }}" onsubmit="return confirm('Remove this block?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </x-card>
    </div>
</x-app-layout>
