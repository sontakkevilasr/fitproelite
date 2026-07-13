<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Interest Levels" subtitle="How hot a lead is (Hot / Warm / Cold).">
            <x-slot name="actions">
                <a href="{{ route('admin.interest-levels.create') }}">
                    <x-primary-button>+ New Level</x-primary-button>
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    @if(session('status'))
        <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3">{{ session('status') }}</div>
    @endif

    <x-card :padded="false">
        @if($interestLevels->isEmpty())
            <x-empty-state title="No interest levels yet" description="Create your first interest level to get started." />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Name</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Color</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Order</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($interestLevels as $level)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $level->name }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-2">
                                        <span class="h-3 w-3 rounded-full inline-block" style="background-color: {{ $level->color }}"></span>
                                        {{ $level->color }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ $level->sort_order }}</td>
                                <td class="px-4 py-3">
                                    <x-status-badge :status="$level->is_active ? 'active' : 'inactive'" />
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.interest-levels.edit', $level) }}" class="text-primary-600 hover:text-primary-800 font-medium">Edit</a>
                                    <form method="POST" action="{{ route('admin.interest-levels.destroy', $level) }}" class="inline" onsubmit="return confirm('Delete this interest level?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="ml-3 text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
</x-app-layout>
