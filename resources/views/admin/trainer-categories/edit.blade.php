<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Trainer Category" :subtitle="$category->name" />
    </x-slot>

    <x-card class="max-w-xl">
        <form method="POST" action="{{ route('admin.trainer-categories.update', $category) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <x-input-label for="name" value="Name" />
                <x-text-input id="name" name="name" class="mt-1" value="{{ old('name', $category->name) }}" required autofocus />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="description" value="Description" />
                <x-textarea-input id="description" name="description" rows="3" class="mt-1">{{ old('description', $category->description) }}</x-textarea-input>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <div class="flex items-center gap-2">
                <x-checkbox id="is_assessment_category" name="is_assessment_category" value="1" :checked="old('is_assessment_category', $category->is_assessment_category)" />
                <x-input-label for="is_assessment_category" value="This is the pre-trial assessment category" />
            </div>

            <div class="flex items-center gap-2">
                <x-checkbox id="is_active" name="is_active" value="1" :checked="old('is_active', $category->is_active)" />
                <x-input-label for="is_active" value="Active" />
            </div>

            <div class="flex items-center gap-3 pt-2">
                <x-primary-button>Save Changes</x-primary-button>
                <a href="{{ route('admin.trainer-categories.index') }}" class="text-sm text-gray-500 hover:text-gray-800">Cancel</a>
            </div>
        </form>
    </x-card>
</x-app-layout>
