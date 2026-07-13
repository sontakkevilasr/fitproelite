<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="$client->name" :subtitle="$client->phone">
            <x-slot name="actions">
                <x-status-badge :status="$client->status" />
                @if(!$client->trials->where('type', 'pre_visit')->count())
                    <a href="{{ route('booking.category', ['type' => 'pre-visit', 'client' => $client]) }}">
                        <x-primary-button>Schedule Pre-Trial Visit</x-primary-button>
                    </a>
                @endif
            </x-slot>
        </x-page-header>
    </x-slot>

    @if(session('status'))
        <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3">{{ session('status') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 space-y-6">
            <x-card>
                <h3 class="font-semibold text-gray-900 mb-3">Details</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd>{{ $client->email ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Address</dt><dd class="text-right">{{ $client->address ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Package</dt><dd>{{ $client->package?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Interest</dt><dd>{{ $client->interestLevel?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Next follow-up</dt><dd>{{ $client->next_follow_up_at?->format('d M Y') ?? '—' }}</dd></div>
                </dl>
            </x-card>

            @if($client->assessment)
                <x-card>
                    <h3 class="font-semibold text-gray-900 mb-3">Health Assessment</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">First-time gym</dt><dd>{{ $client->assessment->first_time_gym ? 'Yes' : 'No' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Objective</dt><dd>{{ \App\Models\ClientAssessment::OBJECTIVES[$client->assessment->workout_objective] ?? '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Recommended</dt><dd>{{ $client->assessment->recommendedCategory?->name ?? '—' }}</dd></div>
                    </dl>
                    @if($client->assessment->medical_conditions)
                        <p class="mt-2 text-sm text-gray-600"><span class="text-gray-500">Medical:</span> {{ $client->assessment->medical_conditions }}</p>
                    @endif
                </x-card>
            @endif

            <x-card>
                <h3 class="font-semibold text-gray-900 mb-3">Log a Call</h3>
                <form method="POST" action="{{ route('counsellor.clients.calls.store', $client) }}" class="space-y-3">
                    @csrf
                    <div>
                        <x-select-input name="outcome" required class="text-sm">
                            <option value="">Outcome</option>
                            <option value="interested">Interested</option>
                            <option value="not_interested">Not Interested</option>
                            <option value="follow_up_later">Follow Up Later</option>
                            <option value="no_answer">No Answer</option>
                            <option value="converted">Converted</option>
                        </x-select-input>
                    </div>
                    <div>
                        <x-textarea-input name="notes" rows="2" placeholder="Call notes" class="text-sm"></x-textarea-input>
                    </div>
                    <div>
                        <x-input-label for="next_follow_up_at" value="Next follow-up date (if applicable)" />
                        <x-text-input id="next_follow_up_at" type="date" name="next_follow_up_at" class="mt-1 text-sm" />
                    </div>
                    <x-primary-button class="w-full justify-center">Log Call</x-primary-button>
                </form>
            </x-card>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <x-card>
                <h3 class="font-semibold text-gray-900 mb-3">Trials &amp; Visits</h3>
                @if($client->trials->isEmpty())
                    <x-empty-state title="No trials scheduled yet" />
                @else
                    <div class="space-y-4">
                        @foreach($client->trials as $trial)
                            <div class="border border-gray-100 rounded-md p-4">
                                <div class="flex items-center justify-between">
                                    <p class="font-medium text-gray-900">
                                        {{ $trial->type === 'pre_visit' ? 'Pre-Trial Visit' : 'Free Trial' }}
                                        <span class="text-gray-400 font-normal">&middot; {{ $trial->trainerProfile->user->name }}</span>
                                    </p>
                                    <x-status-badge :status="$trial->status" />
                                </div>
                                <ul class="mt-2 text-sm text-gray-600 space-y-1">
                                    @foreach($trial->sessions as $session)
                                        <li class="flex items-center justify-between">
                                            <span>{{ $session->session_date->format('d M Y') }} at {{ \Carbon\Carbon::parse($session->start_time)->format('g:i A') }}</span>
                                            <x-status-badge :status="$session->status" />
                                        </li>
                                    @endforeach
                                </ul>

                                @if($trial->type === 'free_trial' && $trial->status === 'scheduled')
                                    <form method="POST" action="{{ route('counsellor.trials.outcome', $trial) }}" class="mt-3 pt-3 border-t border-gray-100 flex flex-wrap items-center gap-2">
                                        @csrf @method('PATCH')
                                        <input type="text" name="outcome_notes" placeholder="Outcome notes (optional)" class="flex-1 min-w-[160px] border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm">
                                        <button type="submit" name="status" value="converted" class="px-3 py-1.5 text-sm rounded-md bg-emerald-600 text-white hover:bg-emerald-700">Converted</button>
                                        <button type="submit" name="status" value="lost" class="px-3 py-1.5 text-sm rounded-md bg-red-600 text-white hover:bg-red-700">Not Converted</button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>

            <x-card>
                <h3 class="font-semibold text-gray-900 mb-3">Call History</h3>
                @if($client->calls->isEmpty())
                    <x-empty-state title="No calls logged yet" />
                @else
                    <ul class="space-y-4">
                        @foreach($client->calls as $call)
                            <li class="border-l-2 border-gray-100 pl-4">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900">{{ $call->call_date->format('d M Y, g:i A') }}</p>
                                    @if($call->outcome)
                                        <x-status-badge :status="$call->outcome" />
                                    @endif
                                </div>
                                <p class="text-sm text-gray-600">{{ $call->notes }}</p>
                                <p class="text-xs text-gray-400">by {{ $call->counsellor->name }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>
        </div>
    </div>
</x-app-layout>
