<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Packages" subtitle="Membership packages clients can be interested in.">
            <x-slot name="actions">
                <a href="{{ route('admin.packages.create') }}">
                    <x-primary-button>+ New Package</x-primary-button>
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    @if(session('status'))
        <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3">{{ session('status') }}</div>
    @endif

    <x-card :padded="false">
        @if($packages->isEmpty())
            <x-empty-state title="No packages yet" description="Create your first package to get started." />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Name</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Price</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($packages as $package)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $package->name }}</div>
                                    <div class="text-gray-500">{{ Str::limit($package->description, 60) }}</div>
                                </td>
                                <td class="px-4 py-3">{{ $package->price !== null ? '₹'.number_format($package->price, 0) : '—' }}</td>
                                <td class="px-4 py-3">
                                    <x-status-badge :status="$package->is_active ? 'active' : 'inactive'" />
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.packages.edit', $package) }}" class="text-primary-600 hover:text-primary-800 font-medium">Edit</a>
                                    <form method="POST" action="{{ route('admin.packages.destroy', $package) }}" class="inline" onsubmit="return confirm('Delete this package?');">
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
