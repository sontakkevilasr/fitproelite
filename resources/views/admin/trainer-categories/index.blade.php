<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Trainer Categories" subtitle="Functional, Yoga, Aerobics, Pre-Trial Assessment...">
            <x-slot name="actions">
                <a href="{{ route('admin.trainer-categories.create') }}">
                    <x-primary-button>+ New Category</x-primary-button>
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    @if(session('status'))
        <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3">{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3">{{ session('error') }}</div>
    @endif

    <x-card :padded="false">
        @if($categories->isEmpty())
            <x-empty-state title="No categories yet" description="Create your first trainer category to get started." />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Name</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Type</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Trainers</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($categories as $category)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $category->name }}</div>
                                    <div class="text-gray-500">{{ Str::limit($category->description, 60) }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    @if($category->is_assessment_category)
                                        <span class="text-indigo-700 font-medium">Assessment</span>
                                    @else
                                        <span class="text-gray-500">Trial category</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $category->trainer_profiles_count }}</td>
                                <td class="px-4 py-3">
                                    <x-status-badge :status="$category->is_active ? 'active' : 'inactive'" />
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.trainer-categories.edit', $category) }}" class="text-primary-600 hover:text-primary-800 font-medium">Edit</a>
                                    @if($category->trainer_profiles_count === 0)
                                        <form method="POST" action="{{ route('admin.trainer-categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Delete this category?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="ml-3 text-red-600 hover:text-red-800">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
</x-app-layout>
