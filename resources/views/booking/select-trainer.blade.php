<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Select a Trainer" :subtitle="$category->name.' for '.$client->name" />
    </x-slot>

    @if($trainers->isEmpty())
        <x-empty-state title="No trainers in this category" description="Ask an admin to add a trainer to this category." />
    @else
        <div
            x-data="quickSuggest({
                suggestUrl: @js(route('booking.trainers.quick-suggest', ['type' => $type, 'client' => $client, 'category_id' => $category->id])),
                calendarBaseUrl: @js(route('booking.calendar', ['type' => $type, 'client' => $client, 'trainerProfile' => '__TRAINER__'])),
                storeBaseUrl: @js(route('booking.store', ['type' => $type, 'client' => $client, 'trainerProfile' => '__TRAINER__'])),
                sessionsNeeded: {{ $type === 'pre-visit' ? 1 : 3 }},
            })"
            class="mb-8"
        >
            <x-card class="border-primary-200 bg-primary-50/40">
                <p class="font-semibold text-gray-900">What day/time did they ask for?</p>
                <p class="text-sm text-gray-500 mt-0.5">Tell us what the caller said — we'll find the nearest free slot across every trainer automatically.</p>

                <div class="mt-4 flex flex-wrap items-end gap-3">
                    <div class="flex gap-2">
                        <button type="button" @click="setToday()" class="px-3 py-2 text-sm rounded-md border border-gray-300 bg-white hover:bg-gray-50" :class="isToday && 'border-primary-500 text-primary-700 bg-primary-50'">Today</button>
                        <button type="button" @click="setTomorrow()" class="px-3 py-2 text-sm rounded-md border border-gray-300 bg-white hover:bg-gray-50" :class="isTomorrow && 'border-primary-500 text-primary-700 bg-primary-50'">Tomorrow</button>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Date</label>
                        <input type="date" x-model="date" @change="search()" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Preferred time (optional)</label>
                        <input type="time" x-model="time" @change="search()" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm">
                    </div>

                    <x-secondary-button type="button" @click="search()">
                        <span x-show="!loading">Find Nearest Slots</span>
                        <span x-show="loading" x-cloak>Searching…</span>
                    </x-secondary-button>
                </div>

                <div x-show="searched" x-cloak class="mt-5 pt-5 border-t border-primary-100">
                    <template x-if="suggestions.length === 0 && !loading">
                        <p class="text-sm text-gray-500">No free slots found in the next two weeks. Try browsing trainers manually below.</p>
                    </template>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <template x-for="s in suggestions" :key="s.trainer_id + s.date + s.start">
                            <div class="bg-white rounded-lg border border-gray-200 p-4 flex items-center gap-3">
                                <img :src="s.trainer_photo" x-show="s.trainer_photo" class="h-10 w-10 rounded-full object-cover shrink-0">
                                <span x-show="!s.trainer_photo" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-100 text-primary-700 font-semibold text-sm" x-text="s.trainer_name.charAt(0).toUpperCase()"></span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-gray-900 truncate" x-text="s.trainer_name"></p>
                                    <p class="text-sm text-primary-700 font-medium" x-text="s.date_label + ' at ' + s.start_label"></p>
                                    <button type="button" @click="bookSuggestion(s)" class="mt-2 w-full text-center px-3 py-1.5 text-sm rounded-md bg-primary-600 text-white hover:bg-primary-700">
                                        Book This Slot
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </x-card>
        </div>

        <p class="text-sm font-medium text-gray-500 mb-3">Or browse trainers manually</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($trainers as $trainer)
                <a href="{{ route('booking.calendar', ['type' => $type, 'client' => $client, 'trainerProfile' => $trainer]) }}">
                    <x-card class="hover:border-primary-300 transition h-full">
                        <div class="flex items-center gap-3">
                            @if($trainer->photoUrl())
                                <img src="{{ $trainer->photoUrl() }}" alt="{{ $trainer->user->name }}" class="h-12 w-12 rounded-full object-cover">
                            @else
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-primary-100 text-primary-700 font-semibold">
                                    {{ Str::of($trainer->user->name)->substr(0, 1)->upper() }}
                                </span>
                            @endif
                            <div>
                                <p class="font-semibold text-gray-900">{{ $trainer->user->name }}</p>
                                <p class="text-sm text-gray-500">{{ Str::limit($trainer->bio, 60) }}</p>
                            </div>
                        </div>
                    </x-card>
                </a>
            @endforeach
        </div>
    @endif

    @push('scripts')
    <script>
        function quickSuggest(config) {
            return {
                date: new Date().toISOString().slice(0, 10),
                time: '',
                loading: false,
                searched: false,
                suggestions: [],

                get isToday() {
                    return this.date === new Date().toISOString().slice(0, 10);
                },
                get isTomorrow() {
                    const t = new Date();
                    t.setDate(t.getDate() + 1);
                    return this.date === t.toISOString().slice(0, 10);
                },

                setToday() {
                    this.date = new Date().toISOString().slice(0, 10);
                    this.search();
                },
                setTomorrow() {
                    const t = new Date();
                    t.setDate(t.getDate() + 1);
                    this.date = t.toISOString().slice(0, 10);
                    this.search();
                },

                search() {
                    this.loading = true;
                    const url = new URL(config.suggestUrl, window.location.origin);
                    url.searchParams.set('date', this.date);
                    if (this.time) {
                        url.searchParams.set('time', this.time);
                    } else {
                        url.searchParams.delete('time');
                    }

                    fetch(url)
                        .then((r) => r.json())
                        .then((data) => {
                            this.suggestions = data.suggestions;
                            this.searched = true;
                            this.loading = false;
                        });
                },

                bookSuggestion(s) {
                    if (config.sessionsNeeded === 1) {
                        // Pre-visit: exactly one session needed — book it directly, no extra clicks.
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = config.storeBaseUrl.replace('__TRAINER__', s.trainer_id);

                        const fields = {
                            _token: document.querySelector('meta[name=csrf-token]').content,
                            'sessions[0][date]': s.date,
                            'sessions[0][start]': s.start,
                            'sessions[0][end]': s.end,
                        };
                        for (const [name, value] of Object.entries(fields)) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = name;
                            input.value = value;
                            form.appendChild(input);
                        }
                        document.body.appendChild(form);
                        form.submit();
                    } else {
                        // Free trial: 3 sessions needed — hand off to the calendar page
                        // with this slot pre-selected as session 1, so sessions 2/3 get
                        // auto-suggested immediately instead of starting from scratch.
                        const params = new URLSearchParams({ preselect_date: s.date, preselect_start: s.start, preselect_end: s.end });
                        window.location = config.calendarBaseUrl.replace('__TRAINER__', s.trainer_id) + '?' + params.toString();
                    }
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
