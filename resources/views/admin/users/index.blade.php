<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Users" subtitle="Admins, counsellors and trainers with login access.">
            <x-slot name="actions">
                <a href="{{ route('admin.users.create') }}">
                    <x-primary-button>+ New User</x-primary-button>
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    @if(session('status'))
        <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3">{{ session('status') }}</div>
    @endif

    <x-card :padded="false">
        <form method="GET" class="flex flex-wrap gap-3 p-4 border-b border-gray-200">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email"
                   class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm flex-1 min-w-[200px]">
            <select name="role" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm">
                <option value="">All roles</option>
                @foreach(['admin', 'counsellor', 'trainer'] as $role)
                    <option value="{{ $role }}" @selected(request('role') === $role)>{{ ucfirst($role) }}</option>
                @endforeach
            </select>
            <x-secondary-button type="submit">Filter</x-secondary-button>
        </form>

        @if($users->isEmpty())
            <x-empty-state title="No users found" description="Try a different search or create a new user." />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Name</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Role</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Category</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($users as $user)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-gray-500">{{ $user->email }}</div>
                                </td>
                                <td class="px-4 py-3 capitalize">{{ $user->roles->pluck('name')->first() ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $user->trainerProfile?->category?->name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <x-status-badge :status="$user->is_active ? 'active' : 'inactive'" />
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-primary-600 hover:text-primary-800 font-medium">Edit</a>
                                    <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="ml-3 text-gray-500 hover:text-gray-800">
                                            {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100">{{ $users->links() }}</div>
        @endif
    </x-card>
</x-app-layout>
