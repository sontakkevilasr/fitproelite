<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="'Free Trial — '.$trial->client->name">
            <x-slot name="actions">
                <x-status-badge :status="$trial->status" />
            </x-slot>
        </x-page-header>
    </x-slot>

    @if(session('status'))
        <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <x-card>
            <h3 class="font-semibold text-gray-900 mb-3">Client</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Name</dt><dd>{{ $trial->client->name }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Phone</dt><dd>{{ $trial->client->phone }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Package</dt><dd>{{ $trial->client->package?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Total sessions</dt><dd>{{ $trial->total_sessions }}</dd></div>
            </dl>

            <form method="POST" action="{{ route('trainer.free-trials.resend-notification', $trial) }}" class="mt-4">
                @csrf
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-primary-700">
                    Send session details on WhatsApp
                </button>
                <p class="text-xs text-gray-400 mt-1.5">Sends the current schedule to {{ $trial->client->phone }}.</p>
            </form>
        </x-card>

        <x-card class="lg:col-span-2">
            <h3 class="font-semibold text-gray-900 mb-3">Sessions</h3>

            <div class="space-y-3" x-data="{ editing: null }">
                @foreach($trial->sessions as $session)
                    <div class="border border-gray-200 rounded-md p-3">
                        <div class="flex items-center justify-between" x-show="editing !== {{ $session->id }}">
                            <div>
                                <p class="font-medium text-gray-900">
                                    {{ $session->session_date->format('D, d M Y') }} at {{ \Carbon\Carbon::parse($session->start_time)->format('g:i A') }}
                                </p>
                                <p class="text-sm text-gray-500">{{ $session->trainerProfile->user->name }} &middot; {{ $session->category?->name ?? '—' }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <x-status-badge :status="$session->status" />
                                <button type="button" @click="editing = {{ $session->id }}" class="text-sm text-primary-600 hover:text-primary-800 font-medium">Edit</button>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('trainer.free-trials.sessions.update', $session) }}" x-show="editing === {{ $session->id }}" x-cloak class="flex flex-wrap items-end gap-3">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Date</label>
                                <input type="date" name="date" value="{{ $session->session_date->format('Y-m-d') }}" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Start</label>
                                <input type="time" name="start" value="{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">End</label>
                                <input type="time" name="end" value="{{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm">
                            </div>
                            <p class="text-xs text-gray-400 basis-full">Trainer: {{ $session->trainerProfile->user->name }} (unchanged — book a different trial to switch trainers)</p>
                            <button type="submit" class="px-3 py-1.5 text-sm rounded-md bg-primary-600 text-white hover:bg-primary-700">Save</button>
                            <button type="button" @click="editing = null" class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-800">Cancel</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>
</x-app-layout>
