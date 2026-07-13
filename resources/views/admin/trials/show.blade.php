<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="($trial->type === 'pre_visit' ? 'Pre-Trial Visit' : 'Free Trial').' — '.$trial->client->name">
            <x-slot name="actions">
                <x-status-badge :status="$trial->status" />
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <x-card>
            <h3 class="font-semibold text-gray-900 mb-3">Overview</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Client</dt><dd>{{ $trial->client->name }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Trainer</dt><dd>{{ $trial->trainerProfile->user->name }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Category</dt><dd>{{ $trial->category->name }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Counsellor</dt><dd>{{ $trial->counsellor->name }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Booked by</dt><dd>{{ $trial->bookedBy->name }}</dd></div>
            </dl>
            @if($trial->outcome_notes)
                <p class="mt-3 text-sm text-gray-600"><span class="text-gray-500">Outcome notes:</span> {{ $trial->outcome_notes }}</p>
            @endif
        </x-card>

        <x-card class="lg:col-span-2">
            <h3 class="font-semibold text-gray-900 mb-3">Sessions</h3>
            <ul class="space-y-2 text-sm">
                @foreach($trial->sessions as $session)
                    <li class="flex items-center justify-between border-b border-gray-100 pb-2 last:border-0">
                        <span>{{ $session->session_date->format('d M Y') }} at {{ \Carbon\Carbon::parse($session->start_time)->format('g:i A') }}</span>
                        <x-status-badge :status="$session->status" />
                    </li>
                @endforeach
            </ul>

            @if($trial->assessment)
                <h3 class="font-semibold text-gray-900 mt-6 mb-3">Health Assessment</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">First-time gym</dt><dd>{{ $trial->assessment->first_time_gym ? 'Yes' : 'No' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Objective</dt><dd>{{ \App\Models\ClientAssessment::OBJECTIVES[$trial->assessment->workout_objective] ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Recommended</dt><dd>{{ $trial->assessment->recommendedCategory?->name ?? '—' }}</dd></div>
                </dl>
            @endif
        </x-card>
    </div>
</x-app-layout>
