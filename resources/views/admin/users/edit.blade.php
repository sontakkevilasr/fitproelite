<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit User" :subtitle="$user->name" />
    </x-slot>

    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data" x-data="{ role: '{{ old('role', $user->roles->pluck('name')->first() ?? 'counsellor') }}' }" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <x-input-label for="name" value="Full name" />
                <x-text-input id="name" name="name" class="mt-1" value="{{ old('name', $user->name) }}" required autofocus />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" type="email" name="email" class="mt-1" value="{{ old('email', $user->email) }}" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="phone" value="Phone" />
                    <x-text-input id="phone" name="phone" class="mt-1" value="{{ old('phone', $user->phone) }}" />
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="password" value="New password (leave blank to keep current)" />
                <x-text-input id="password" type="password" name="password" class="mt-1" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="role" value="Role" />
                <x-select-input id="role" name="role" x-model="role" class="mt-1" required>
                    <option value="counsellor" @selected(($user->roles->pluck('name')->first() ?? '') === 'counsellor')>Counsellor</option>
                    <option value="trainer" @selected(($user->roles->pluck('name')->first() ?? '') === 'trainer')>Trainer</option>
                    <option value="admin" @selected(($user->roles->pluck('name')->first() ?? '') === 'admin')>Admin</option>
                </x-select-input>
                <x-input-error :messages="$errors->get('role')" class="mt-2" />
            </div>

            <div x-show="role === 'trainer'" x-cloak class="space-y-5 border-t border-gray-100 pt-5">
                @if($user->trainerProfile?->photoUrl())
                    <img src="{{ $user->trainerProfile->photoUrl() }}" alt="{{ $user->name }}" class="h-16 w-16 rounded-full object-cover">
                @endif

                <div>
                    <x-input-label for="trainer_category_id" value="Trainer category" />
                    <x-select-input id="trainer_category_id" name="trainer_category_id" class="mt-1">
                        <option value="">Select a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('trainer_category_id', $user->trainerProfile?->trainer_category_id) == $category->id)>
                                {{ $category->name }}{{ $category->is_assessment_category ? ' (Assessment)' : '' }}
                            </option>
                        @endforeach
                    </x-select-input>
                    <x-input-error :messages="$errors->get('trainer_category_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="session_duration_minutes" value="Session duration (minutes)" />
                    <x-text-input id="session_duration_minutes" type="number" name="session_duration_minutes" class="mt-1" value="{{ old('session_duration_minutes', $user->trainerProfile?->session_duration_minutes ?? 60) }}" />
                </div>

                <div>
                    <x-input-label for="bio" value="Bio" />
                    <x-textarea-input id="bio" name="bio" rows="3" class="mt-1">{{ old('bio', $user->trainerProfile?->bio) }}</x-textarea-input>
                </div>

                <div>
                    <x-input-label for="photo" value="Profile photo" />
                    <input id="photo" type="file" name="photo" accept="image/*" class="mt-1 block w-full text-sm text-gray-600">
                    <x-input-error :messages="$errors->get('photo')" class="mt-2" />
                </div>
            </div>

            <div class="flex items-center gap-2">
                <x-checkbox id="is_active" name="is_active" value="1" :checked="$user->is_active" />
                <x-input-label for="is_active" value="Active (can log in)" />
            </div>

            <div class="flex items-center gap-3 pt-2">
                <x-primary-button>Save Changes</x-primary-button>
                <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-500 hover:text-gray-800">Cancel</a>
            </div>
        </form>
    </x-card>
</x-app-layout>
