<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Pick Free Slots" :subtitle="$trainer->user->name.' &middot; '.$client->name" />
    </x-slot>

    @if($errors->any())
        <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div
        data-type="{{ $type }}"
        data-sessions-needed="{{ $sessionsNeeded }}"
        data-category-id="{{ $categoryId }}"
        data-suggest-url="{{ route('booking.suggest', ['type' => $type, 'client' => $client, 'trainerProfile' => $trainer]) }}"
        x-data="bookingCalendar($el.dataset)"
        class="grid grid-cols-1 lg:grid-cols-3 gap-6"
    >

        <div class="lg:col-span-2">
            <x-card :padded="false">
                <div class="p-2">
                    <x-calendar :events-url="route('booking.slots', ['type' => $type, 'client' => $client, 'trainerProfile' => $trainer])"
                                @calendar:event-click="onEventClick($event.detail)" />
                </div>
            </x-card>
        </div>

        <div>
            <x-card>
                <h3 class="font-semibold text-gray-900 mb-1">
                    {{ $type === 'pre-visit' ? 'Visit Slot' : 'Trial Sessions' }}
                </h3>
                <p class="text-xs text-gray-400 mb-4">Click a green (free) slot on the calendar.</p>

                <template x-for="(session, i) in sessions" :key="i">
                    <div class="mb-3 p-3 rounded-md border text-sm"
                         :class="i === activeIndex ? 'border-primary-400 bg-primary-50' : 'border-gray-200'">
                        <p class="font-medium text-gray-700 mb-1">
                            <span x-show="sessionsNeeded > 1">Session <span x-text="i + 1"></span></span>
                            <span x-show="sessionsNeeded === 1">Visit</span>
                        </p>
                        <template x-if="session">
                            <div class="flex items-center justify-between text-gray-600">
                                <span x-text="session.date + ' at ' + session.start"></span>
                                <button type="button" class="text-primary-600 text-xs font-medium" @click="editSlot(i)">Change</button>
                            </div>
                        </template>
                        <template x-if="!session">
                            <p class="text-xs text-gray-400" x-text="i === activeIndex ? 'Waiting for you to click a slot…' : 'Not selected yet'"></p>
                        </template>
                    </div>
                </template>

                <form method="POST" action="{{ route('booking.store', ['type' => $type, 'client' => $client, 'trainerProfile' => $trainer, 'category_id' => $categoryId]) }}">
                    @csrf
                    <template x-for="(session, i) in sessions" :key="'fields-' + i">
                        <span>
                            <input type="hidden" :name="`sessions[${i}][date]`" :value="session ? session.date : ''">
                            <input type="hidden" :name="`sessions[${i}][start]`" :value="session ? session.start : ''">
                            <input type="hidden" :name="`sessions[${i}][end]`" :value="session ? session.end : ''">
                        </span>
                    </template>
                    <button type="submit" class="w-full mt-2 inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-primary-700 disabled:opacity-40 disabled:cursor-not-allowed"
                            :disabled="!allFilled()">
                        Confirm Booking
                    </button>
                </form>
            </x-card>
        </div>
    </div>

    @push('scripts')
    <script>
        function bookingCalendar(config) {
            const sessionsNeeded = parseInt(config.sessionsNeeded, 10);
            return {
                type: config.type,
                sessionsNeeded,
                categoryId: config.categoryId,
                suggestUrl: config.suggestUrl,
                sessions: Array.from({ length: sessionsNeeded }, () => null),
                activeIndex: 0,

                init() {
                    // Arrived here from the "quick suggest" screen with a slot
                    // already picked out (e.g. session 1 of a free trial) —
                    // pre-fill it and immediately fetch suggestions for the
                    // rest, same as if the user had clicked that calendar cell.
                    const params = new URLSearchParams(window.location.search);
                    const date = params.get('preselect_date');
                    const start = params.get('preselect_start');
                    const end = params.get('preselect_end');

                    if (date && start && end) {
                        this.sessions[0] = { date, start, end };

                        if (this.type === 'free-trial' && this.sessionsNeeded > 1) {
                            this.fetchSuggestions(date, start);
                        } else {
                            this.advanceActiveIndex();
                        }
                    }
                },

                onEventClick({ event }) {
                    if (event.extendedProps.type !== 'free') return;

                    const start = event.start;
                    const end = event.end;
                    const pad = (n) => String(n).padStart(2, '0');
                    const date = `${start.getFullYear()}-${pad(start.getMonth() + 1)}-${pad(start.getDate())}`;
                    const startTime = `${pad(start.getHours())}:${pad(start.getMinutes())}`;
                    const endTime = `${pad(end.getHours())}:${pad(end.getMinutes())}`;

                    this.sessions[this.activeIndex] = { date, start: startTime, end: endTime };

                    if (this.type === 'free-trial' && this.activeIndex === 0 && this.sessionsNeeded > 1) {
                        this.fetchSuggestions(date, startTime);
                    } else {
                        this.advanceActiveIndex();
                    }
                },

                fetchSuggestions(date, start) {
                    fetch(this.suggestUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            Accept: 'application/json',
                        },
                        body: JSON.stringify({ date, start, category_id: this.categoryId }),
                    })
                        .then((r) => r.json())
                        .then((data) => {
                            (data.sessions || []).forEach((slot, i) => {
                                const target = i + 1; // index 0 is the slot the user just clicked
                                if (this.sessions[target] === null) {
                                    this.sessions[target] = { date: slot.date, start: slot.start, end: slot.end };
                                }
                            });
                            this.advanceActiveIndex();
                        });
                },

                advanceActiveIndex() {
                    const nextNull = this.sessions.findIndex((s) => s === null);
                    this.activeIndex = nextNull === -1 ? this.sessions.length - 1 : nextNull;
                },

                editSlot(i) {
                    this.activeIndex = i;
                },

                allFilled() {
                    return this.sessions.every((s) => s !== null);
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
