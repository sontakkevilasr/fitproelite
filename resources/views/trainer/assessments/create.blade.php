<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Health Assessment" :subtitle="'For '.$client->name" />
    </x-slot>

    <x-card class="max-w-xl mb-6 bg-primary-50/40 border-primary-200">
        <h3 class="font-semibold text-gray-900 mb-3">What the client already told us</h3>
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between gap-4">
                <dt class="text-gray-500 shrink-0">Phone</dt>
                <dd class="text-right">{{ $client->phone }}</dd>
            </div>
            @if($client->address)
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500 shrink-0">Address</dt>
                    <dd class="text-right">{{ $client->address }}</dd>
                </div>
            @endif
            <div class="flex justify-between gap-4">
                <dt class="text-gray-500 shrink-0">Current package interest</dt>
                <dd class="text-right font-medium text-primary-700">{{ $client->package?->name ?? 'Not specified yet' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-gray-500 shrink-0">How interested</dt>
                <dd class="text-right">
                    @if($client->interestLevel)
                        <span class="inline-flex items-center gap-1.5">
                            <span class="h-2 w-2 rounded-full" style="background-color: {{ $client->interestLevel->color }}"></span>
                            {{ $client->interestLevel->name }}
                        </span>
                    @else
                        —
                    @endif
                </dd>
            </div>
        </dl>

        @if($client->calls->isNotEmpty())
            <div class="mt-4 pt-4 border-t border-primary-100">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400 mb-2">Latest call notes ({{ $client->calls->first()->call_date->format('d M Y') }})</p>
                <p class="text-sm text-gray-700">{{ $client->calls->first()->notes ?: 'No notes recorded.' }}</p>
            </div>
        @endif
    </x-card>

    <x-card class="max-w-xl">
        <form method="POST" action="{{ route('trainer.assessments.store', $trial) }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="package_id" value="Package interested in" />
                <x-select-input id="package_id" name="package_id" class="mt-1">
                    <option value="">Not decided yet</option>
                    @foreach($packages as $package)
                        <option value="{{ $package->id }}" @selected(old('package_id', $client->package_id) == $package->id)>
                            {{ $package->name }} &middot; {{ $package->trial_sessions_count ?? 0 }} trial session(s)
                        </option>
                    @endforeach
                </x-select-input>
                <p class="text-xs text-gray-400 mt-1">Client can change their mind about the package during the visit — update it here if so.</p>
                <x-input-error :messages="$errors->get('package_id')" class="mt-2" />
            </div>

            <div class="flex items-center gap-2">
                <x-checkbox id="first_time_gym" name="first_time_gym" value="1" :checked="old('first_time_gym')" />
                <x-input-label for="first_time_gym" value="This is their first time working out" />
            </div>

            <div>
                <x-input-label for="workout_objective" value="Workout objective" />
                <x-select-input id="workout_objective" name="workout_objective" class="mt-1" required>
                    <option value="">Select an objective</option>
                    @foreach($objectives as $value => $label)
                        <option value="{{ $value }}" @selected(old('workout_objective') === $value)>{{ $label }}</option>
                    @endforeach
                </x-select-input>
                <x-input-error :messages="$errors->get('workout_objective')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="medical_conditions" value="Medical conditions / injuries (if any)" />
                <x-textarea-input id="medical_conditions" name="medical_conditions" rows="3" class="mt-1">{{ old('medical_conditions') }}</x-textarea-input>
            </div>

            <div>
                <x-input-label for="notes" value="Other notes" />
                <x-textarea-input id="notes" name="notes" rows="3" class="mt-1">{{ old('notes') }}</x-textarea-input>
            </div>

            <div>
                <x-input-label for="recommended_category_id" value="Recommended trial category" />
                <x-select-input id="recommended_category_id" name="recommended_category_id" class="mt-1" required>
                    <option value="">Select a category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('recommended_category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </x-select-input>
                <x-input-error :messages="$errors->get('recommended_category_id')" class="mt-2" />
            </div>

            <div class="pt-2">
                <x-primary-button>Save &amp; Continue to Book Trial</x-primary-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
