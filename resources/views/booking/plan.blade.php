<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Free Trial Suggestions" :subtitle="'For '.$client->name.' — based on their package'" />
    </x-slot>

    @if(session('error'))
        <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3">{{ session('error') }}</div>
    @endif

    @if(! $package || $maxSessions <= 0 || $sections->isEmpty())
        <x-empty-state title="No suggestion available" description="This client has no package with trial sessions / trainer types configured, so there's nothing to suggest. Browse trainer types manually instead." />
        <div class="mt-4">
            <a href="{{ route('booking.category', ['type' => $type, 'client' => $client]) }}" class="text-primary-600 hover:text-primary-800 font-medium text-sm">
                Browse all trainer types &rarr;
            </a>
        </div>
    @else
        <div x-data="planPicker({ maxSessions: {{ $maxSessions }} })">
            <p class="text-sm text-gray-500 mb-4">
                {{ $package->name }} allows up to {{ $maxSessions }} free trial session{{ $maxSessions > 1 ? 's' : '' }}.
                Every open slot in the period below is shown — pick any combination: same trainer for all of them, or a mix across categories, whatever fits their availability.
            </p>

            <x-card class="mb-5 sticky top-4 z-10">
                <form method="GET" class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">From date</label>
                        <input type="date" name="from" value="{{ $from }}" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">To date</label>
                        <input type="date" name="to" value="{{ $to }}" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm">
                    </div>
                    <button type="submit" class="px-3 py-2 text-sm rounded-md border border-gray-300 bg-white hover:bg-gray-50">Update period</button>

                    <div class="border-l border-gray-200 pl-4 ml-1">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Category</label>
                        <select x-model="filterCategory" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm">
                            <option value="">All categories</option>
                            @foreach($sections as $section)
                                <option value="{{ $section['category']->id }}">{{ $section['category']->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">From time</label>
                        <input type="time" x-model="filterTimeFrom" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">To time</label>
                        <input type="time" x-model="filterTimeTo" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm">
                    </div>
                    <div class="flex-1 min-w-[10rem]">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Trainer name</label>
                        <input type="text" x-model="filterTrainerName" placeholder="Search trainer…" class="w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm">
                    </div>
                    <button type="button" @click="filterCategory = ''; filterTimeFrom = ''; filterTimeTo = ''; filterTrainerName = ''" class="text-sm text-gray-400 hover:text-gray-600">Clear filters</button>

                    <div class="ml-auto flex items-center gap-3">
                        <span class="text-sm font-medium" :class="atCap() ? 'text-amber-600' : 'text-gray-600'">
                            <span x-text="selected.length"></span> of {{ $maxSessions }} selected
                        </span>
                        <button type="button" @click="submitBooking()" class="px-4 py-2 text-sm rounded-md bg-primary-600 text-white hover:bg-primary-700 disabled:opacity-40 disabled:cursor-not-allowed" :disabled="selected.length === 0">
                            Confirm booking
                        </button>
                    </div>
                </form>
            </x-card>

            <form method="POST" action="{{ route('booking.plan.book', ['type' => $type, 'client' => $client]) }}" x-ref="bookForm">
                @csrf

                @foreach($sections as $section)
                    @php $category = $section['category']; @endphp
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-semibold text-gray-900">{{ $category->name }}</h3>
                            <a href="{{ route('booking.trainers', ['type' => $type, 'client' => $client, 'category_id' => $category->id]) }}" class="text-xs text-gray-400 hover:text-gray-600">
                                Browse full calendars &rarr;
                            </a>
                        </div>

                        @if($section['cards']->isEmpty())
                            <p class="text-sm text-gray-400">No trainer with an open slot in this period for this category.</p>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($section['cards'] as $card)
                                    @php
                                        $key = $category->id.'|'.$card['trainer']->id.'|'.$card['date'].'|'.$card['start'];
                                        $dateLabel = \Carbon\Carbon::parse($card['date'])->isToday() ? 'Today' : (\Carbon\Carbon::parse($card['date'])->isTomorrow() ? 'Tomorrow' : \Carbon\Carbon::parse($card['date'])->format('D, d M'));
                                    @endphp
                                    <button
                                        type="button"
                                        @click="toggle('{{ $key }}')"
                                        x-show="matches('{{ $category->id }}', '{{ addslashes($card['trainer']->user->name) }}', '{{ $card['start'] }}')"
                                        :class="isSelected('{{ $key }}') ? 'border-primary-500 ring-2 ring-primary-200 bg-primary-50/40' : 'border-gray-200 hover:border-primary-300'"
                                        class="text-left border rounded-lg p-3 transition relative"
                                        data-key="{{ $key }}"
                                        data-trainer-profile-id="{{ $card['trainer']->id }}"
                                        data-category-id="{{ $category->id }}"
                                        data-date="{{ $card['date'] }}"
                                        data-start="{{ $card['start'] }}"
                                        data-end="{{ $card['end'] }}"
                                    >
                                        <span x-show="isSelected('{{ $key }}')" class="absolute top-2 right-2 h-5 w-5 rounded-full bg-primary-600 text-white text-xs flex items-center justify-center">&check;</span>
                                        <div class="flex items-center gap-3">
                                            @if($card['trainer']->photoUrl())
                                                <img src="{{ $card['trainer']->photoUrl() }}" alt="{{ $card['trainer']->user->name }}" class="h-10 w-10 rounded-full object-cover shrink-0">
                                            @else
                                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-100 text-primary-700 font-semibold text-sm">
                                                    {{ Str::of($card['trainer']->user->name)->substr(0, 1)->upper() }}
                                                </span>
                                            @endif
                                            <div class="min-w-0 flex-1">
                                                <p class="font-medium text-gray-900 truncate">{{ $card['trainer']->user->name }}</p>
                                                <p class="text-sm text-primary-700 font-medium">{{ $dateLabel }} at {{ \Carbon\Carbon::parse($card['start'])->format('g:i A') }}</p>
                                            </div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </form>
        </div>
    @endif

    @push('scripts')
    <script>
        function planPicker(config) {
            return {
                maxSessions: config.maxSessions,
                selected: [],
                filterCategory: '',
                filterTimeFrom: '',
                filterTimeTo: '',
                filterTrainerName: '',

                toggle(key) {
                    const idx = this.selected.indexOf(key);
                    if (idx !== -1) {
                        this.selected.splice(idx, 1);
                        return;
                    }
                    if (this.selected.length >= this.maxSessions) return;
                    this.selected.push(key);
                },

                isSelected(key) {
                    return this.selected.includes(key);
                },

                atCap() {
                    return this.selected.length >= this.maxSessions;
                },

                matches(categoryId, trainerName, start) {
                    if (this.filterCategory && String(categoryId) !== String(this.filterCategory)) return false;
                    if (this.filterTrainerName && !trainerName.toLowerCase().includes(this.filterTrainerName.toLowerCase())) return false;
                    if (this.filterTimeFrom && start < this.filterTimeFrom) return false;
                    if (this.filterTimeTo && start > this.filterTimeTo) return false;
                    return true;
                },

                submitBooking() {
                    const form = this.$refs.bookForm;
                    form.querySelectorAll('[data-generated]').forEach((el) => el.remove());

                    this.selected.forEach((key, i) => {
                        const btn = form.querySelector(`[data-key="${CSS.escape(key)}"]`);
                        if (!btn) return;

                        const fields = {
                            [`sessions[${i}][trainer_profile_id]`]: btn.dataset.trainerProfileId,
                            [`sessions[${i}][category_id]`]: btn.dataset.categoryId,
                            [`sessions[${i}][date]`]: btn.dataset.date,
                            [`sessions[${i}][start]`]: btn.dataset.start,
                            [`sessions[${i}][end]`]: btn.dataset.end,
                        };
                        for (const [name, value] of Object.entries(fields)) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = name;
                            input.value = value;
                            input.dataset.generated = 'true';
                            form.appendChild(input);
                        }
                    });

                    form.submit();
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
