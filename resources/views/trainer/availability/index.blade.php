<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Weekly Availability" subtitle="Set the hours you're free for trials each week." />
    </x-slot>

    @if(session('status'))
        <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3">{{ session('status') }}</div>
    @endif

    @if(!$trainer)
        <x-empty-state title="No trainer profile" description="Ask an admin to link your account to a trainer category." />
    @else
        <script type="application/json" id="existing-weekly-slots">{!! $weeklySlots->groupBy('day_of_week')->toJson() !!}</script>

        <x-card x-data="availabilityEditor()">
            <form method="POST" action="{{ route('trainer.availability.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <template x-for="(day, dayIndex) in days" :key="day.value">
                    <div class="border-b border-gray-100 last:border-0 pb-5 last:pb-0">
                        <div class="flex items-center justify-between mb-2">
                            <p class="font-medium text-gray-900" x-text="day.label"></p>
                            <button type="button" class="text-sm text-primary-600 hover:text-primary-800" @click="addRange(dayIndex)">+ Add time range</button>
                        </div>

                        <template x-if="day.ranges.length === 0">
                            <p class="text-sm text-gray-400">Not available</p>
                        </template>

                        <div class="space-y-2">
                            <template x-for="(range, rangeIndex) in day.ranges" :key="rangeIndex">
                                <div class="flex items-center gap-2">
                                    <input type="time" :name="`slots[${day.value}_${rangeIndex}][start_time]`" x-model="range.start_time" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm">
                                    <span class="text-gray-400">to</span>
                                    <input type="time" :name="`slots[${day.value}_${rangeIndex}][end_time]`" x-model="range.end_time" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm">
                                    <input type="hidden" :name="`slots[${day.value}_${rangeIndex}][day_of_week]`" :value="day.value">
                                    <button type="button" class="text-red-500 hover:text-red-700 text-sm" @click="day.ranges.splice(rangeIndex, 1)">Remove</button>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <x-primary-button>Save Availability</x-primary-button>
            </form>
        </x-card>
    @endif

    @once
        @push('scripts')
        <script>
            function availabilityEditor() {
                const existing = JSON.parse(document.getElementById('existing-weekly-slots').textContent);
                const dayLabels = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                return {
                    days: dayLabels.map((label, value) => ({
                        value,
                        label,
                        ranges: (existing[value] || []).map(s => ({ start_time: s.start_time.slice(0,5), end_time: s.end_time.slice(0,5) })),
                    })),
                    addRange(dayIndex) {
                        this.days[dayIndex].ranges.push({ start_time: '09:00', end_time: '10:00' });
                    },
                };
            }
        </script>
        @endpush
    @endonce
</x-app-layout>
