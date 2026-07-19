<x-app-layout>
    <x-slot name="header">
        <x-page-header title="New Package" />
    </x-slot>

    <div
        x-data="packageForm({ weekDays: 3, rows: [{ trainer_category_id: '', sessions: '' }] })"
        class="max-w-2xl"
    >
        <x-card>
            <form method="POST" action="{{ route('admin.packages.store') }}" class="space-y-5">
                @csrf

                <div>
                    <x-input-label for="name" value="Name" />
                    <x-text-input id="name" name="name" class="mt-1" value="{{ old('name') }}" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="price" value="Price (₹)" />
                    <x-text-input id="price" type="number" step="0.01" name="price" class="mt-1" value="{{ old('price') }}" />
                    <x-input-error :messages="$errors->get('price')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="description" value="Description" />
                    <x-textarea-input id="description" name="description" rows="2" class="mt-1">{{ old('description') }}</x-textarea-input>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div>
                    <x-input-label value="Week type" />
                    <div class="mt-2 flex gap-4">
                        <label class="flex items-center gap-2 text-sm">
                            <input type="radio" name="week_days" value="3" x-model.number="weekDays" class="text-primary-600 focus:ring-primary-500" {{ old('week_days', 3) == 3 ? 'checked' : '' }}>
                            3 Days
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="radio" name="week_days" value="6" x-model.number="weekDays" class="text-primary-600 focus:ring-primary-500" {{ old('week_days') == 6 ? 'checked' : '' }}>
                            6 Days Weekly
                        </label>
                    </div>
                    <x-input-error :messages="$errors->get('week_days')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="trial_sessions_count" value="No. of trial sessions" />
                    <x-text-input id="trial_sessions_count" type="number" min="1" max="10" name="trial_sessions_count" class="mt-1 w-32" value="{{ old('trial_sessions_count', 3) }}" />
                    <p class="mt-1 text-xs text-gray-400">For reference only right now — every free trial still books 3 sessions regardless of this value.</p>
                    <x-input-error :messages="$errors->get('trial_sessions_count')" class="mt-2" />
                </div>

                <div class="border-t border-gray-100 pt-5">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <x-input-label value="Type of trainer & sessions" />
                            <p class="text-xs text-gray-400">How many sessions of each trainer type make up this package.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-[1fr_120px_40px] gap-3 mb-2 text-xs font-medium text-gray-500 uppercase tracking-wide">
                        <span>Type of Trainer</span>
                        <span>Session Type</span>
                        <span></span>
                    </div>

                    <template x-for="(row, index) in rows" :key="index">
                        <div class="grid grid-cols-[1fr_120px_40px] gap-3 mb-2 items-start">
                            <select :name="`rows[${index}][trainer_category_id]`" x-model="row.trainer_category_id" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm w-full" required>
                                <option value="">Select…</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <input type="number" min="1" max="100" :name="`rows[${index}][sessions]`" x-model.number="row.sessions" placeholder="e.g. 8" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm w-full" required>
                            <button type="button" @click="removeRow(index)" x-show="rows.length > 1" class="text-red-500 hover:text-red-700 text-sm mt-2">Delete</button>
                        </div>
                    </template>

                    <button type="button" @click="addRow()" class="mt-2 px-3 py-1.5 text-sm rounded-md bg-primary-50 text-primary-700 border border-primary-200 hover:bg-primary-100 font-medium">
                        + Add
                    </button>

                    <x-input-error :messages="$errors->get('rows')" class="mt-3" />

                    <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Total Sessions</span>
                        <div class="flex items-center gap-2">
                            <input type="text" readonly :value="totalSessions()" class="w-20 text-center border-gray-300 bg-gray-50 rounded-md shadow-sm text-sm font-semibold" :class="isOverLimit ? 'text-red-600 border-red-300' : 'text-gray-900'">
                            <span class="text-xs text-gray-400">/ <span x-text="maxSessions()"></span> max</span>
                        </div>
                    </div>
                    <p x-show="isOverLimit" x-cloak class="mt-2 text-sm text-red-600">
                        Over the limit for <span x-text="weekDays"></span> days/week — reduce a session count or add another day.
                    </p>
                </div>

                <div class="flex items-center gap-2 border-t border-gray-100 pt-5">
                    <x-checkbox id="is_active" name="is_active" value="1" checked />
                    <x-input-label for="is_active" value="Active" />
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-primary-button type="submit" ::disabled="isOverLimit">Create Package</x-primary-button>
                    <a href="{{ route('admin.packages.index') }}" class="text-sm text-gray-500 hover:text-gray-800">Cancel</a>
                </div>
            </form>
        </x-card>
    </div>

    @push('scripts')
    <script>
        function packageForm(config) {
            return {
                weekDays: config.weekDays,
                rows: config.rows,
                maxSessions() {
                    return this.weekDays * 4;
                },
                totalSessions() {
                    return this.rows.reduce((sum, r) => sum + (parseInt(r.sessions) || 0), 0);
                },
                get isOverLimit() {
                    return this.totalSessions() > this.maxSessions();
                },
                addRow() {
                    this.rows.push({ trainer_category_id: '', sessions: '' });
                },
                removeRow(index) {
                    this.rows.splice(index, 1);
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
