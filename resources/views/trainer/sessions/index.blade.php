<x-app-layout>
    <x-slot name="header">
        <x-page-header title="My Sessions" subtitle="Mark attendance for pre-trial visits and trial sessions." />
    </x-slot>

    @if(session('status'))
        <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3">{{ session('status') }}</div>
    @endif

    <x-card :padded="false">
        @if($sessions->isEmpty())
            <x-empty-state title="No sessions yet" description="Sessions booked with you will show up here." />
        @else
            <div class="divide-y divide-gray-100">
                @foreach($sessions as $session)
                    <div class="p-4" x-data="{ open: false }">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <p class="font-medium text-gray-900">
                                    {{ $session->trial->client->name }}
                                    <span class="text-gray-400 font-normal">&middot; {{ $session->trial->type === 'pre_visit' ? 'Pre-Trial Visit' : 'Session '.$session->session_number.'/'.$session->trial->total_sessions }}</span>
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ $session->session_date->format('D, d M Y') }} &middot; {{ \Carbon\Carbon::parse($session->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($session->end_time)->format('g:i A') }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-status-badge :status="$session->status" />
                                @if($session->status === 'scheduled')
                                    <button @click="open = !open" class="text-sm text-primary-600 hover:text-primary-800 font-medium">Update</button>
                                @endif
                            </div>
                        </div>

                        <div x-show="open" x-cloak class="mt-3 pt-3 border-t border-gray-100">
                            <form method="POST" action="{{ route('trainer.sessions.status', $session) }}" class="space-y-3">
                                @csrf
                                @method('PATCH')
                                <textarea name="notes" rows="2" placeholder="Notes (optional)" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm w-full text-sm"></textarea>
                                <div class="flex flex-wrap gap-2">
                                    <button type="submit" name="status" value="completed" class="px-3 py-1.5 text-sm rounded-md bg-emerald-600 text-white hover:bg-emerald-700">Mark Completed</button>
                                    <button type="submit" name="status" value="no_show" class="px-3 py-1.5 text-sm rounded-md bg-amber-500 text-white hover:bg-amber-600">No Show</button>
                                    <button type="submit" name="status" value="cancelled" class="px-3 py-1.5 text-sm rounded-md bg-red-600 text-white hover:bg-red-700">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="p-4 border-t border-gray-100">{{ $sessions->links() }}</div>
        @endif
    </x-card>
</x-app-layout>
