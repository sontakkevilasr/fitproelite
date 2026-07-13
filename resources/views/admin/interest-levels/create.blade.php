<x-app-layout>
    <x-slot name="header">
        <x-page-header title="New Interest Level" />
    </x-slot>

    <x-card class="max-w-xl">
        <form method="POST" action="{{ route('admin.interest-levels.store') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="name" value="Name" />
                <x-text-input id="name" name="name" class="mt-1" value="{{ old('name') }}" required autofocus />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="color" value="Color" />
                <input id="color" type="color" name="color" value="{{ old('color', '#6b7280') }}" class="mt-1 h-10 w-20 rounded border-gray-300">
                <x-input-error :messages="$errors->get('color')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="sort_order" value="Sort order" />
                <x-text-input id="sort_order" type="number" name="sort_order" class="mt-1" value="{{ old('sort_order', 0) }}" />
                <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
            </div>

            <div class="flex items-center gap-2">
                <x-checkbox id="is_active" name="is_active" value="1" checked />
                <x-input-label for="is_active" value="Active" />
            </div>

            <div class="flex items-center gap-3 pt-2">
                <x-primary-button>Create Level</x-primary-button>
                <a href="{{ route('admin.interest-levels.index') }}" class="text-sm text-gray-500 hover:text-gray-800">Cancel</a>
            </div>
        </form>
    </x-card>
</x-app-layout>
