<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Reports" subtitle="Trial activity and conversion performance." />
    </x-slot>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Pre-Visits Scheduled" :value="$stats['preVisitsScheduled']" />
        <x-stat-card label="Free Trials Scheduled" :value="$stats['freeTrialsScheduled']" />
        <x-stat-card label="Sessions Completed" :value="$stats['sessionsCompleted']" />
        <x-stat-card label="No-Shows" :value="$stats['sessionsNoShow']" />
        <x-stat-card label="Converted" :value="$stats['converted']" />
        <x-stat-card label="Lost" :value="$stats['lost']" />
        <x-stat-card label="Conversion Rate" :value="$stats['conversionRate'].'%'" />
        <x-stat-card label="Awaiting Follow-up" :value="$stats['followUps']" />
    </div>

    <x-card>
        <h3 class="font-semibold text-gray-900 mb-3">Free Trials by Category</h3>
        @if($byCategory->isEmpty())
            <x-empty-state title="No free trials booked yet" />
        @else
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead>
                    <tr>
                        <th class="py-2 text-left font-medium text-gray-500">Category</th>
                        <th class="py-2 text-left font-medium text-gray-500">Total Trials</th>
                        <th class="py-2 text-left font-medium text-gray-500">Converted</th>
                        <th class="py-2 text-left font-medium text-gray-500">Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($byCategory as $categoryName => $data)
                        <tr>
                            <td class="py-2 font-medium text-gray-900">{{ $categoryName }}</td>
                            <td class="py-2">{{ $data['total'] }}</td>
                            <td class="py-2">{{ $data['converted'] }}</td>
                            <td class="py-2">{{ $data['total'] > 0 ? round(($data['converted'] / $data['total']) * 100, 1) : 0 }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-card>
</x-app-layout>
