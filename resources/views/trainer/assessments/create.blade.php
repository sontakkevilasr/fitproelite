<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Health Assessment" :subtitle="'For '.$client->name" />
    </x-slot>

    <x-card class="max-w-xl">
        <form method="POST" action="{{ route('trainer.assessments.store', $trial) }}" class="space-y-5">
            @csrf

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
