<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Package" :subtitle="$package->name" />
    </x-slot>

    <x-card class="max-w-xl">
        <form method="POST" action="{{ route('admin.packages.update', $package) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <x-input-label for="name" value="Name" />
                <x-text-input id="name" name="name" class="mt-1" value="{{ old('name', $package->name) }}" required autofocus />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="price" value="Price (₹)" />
                <x-text-input id="price" type="number" step="0.01" name="price" class="mt-1" value="{{ old('price', $package->price) }}" />
                <x-input-error :messages="$errors->get('price')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="description" value="Description" />
                <x-textarea-input id="description" name="description" rows="3" class="mt-1">{{ old('description', $package->description) }}</x-textarea-input>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <div class="flex items-center gap-2">
                <x-checkbox id="is_active" name="is_active" value="1" :checked="old('is_active', $package->is_active)" />
                <x-input-label for="is_active" value="Active" />
            </div>

            <div class="flex items-center gap-3 pt-2">
                <x-primary-button>Save Changes</x-primary-button>
                <a href="{{ route('admin.packages.index') }}" class="text-sm text-gray-500 hover:text-gray-800">Cancel</a>
            </div>
        </form>
    </x-card>
</x-app-layout>
