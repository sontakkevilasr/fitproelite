<x-app-layout>
    <x-slot name="header">
        <x-page-header title="WhatsApp Notifications" subtitle="Messages logged for trainers and clients on every booking." />
    </x-slot>

    <form method="GET" class="mb-4">
        <select name="recipient_type" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
            <option value="">All recipients</option>
            <option value="trainer" @selected(request('recipient_type') === 'trainer')>Trainer</option>
            <option value="client" @selected(request('recipient_type') === 'client')>Client</option>
        </select>
    </form>

    <x-card :padded="false">
        @if($logs->isEmpty())
            <x-empty-state title="No notifications yet" description="Notifications appear here whenever a visit or trial is booked." />
        @else
            <div class="divide-y divide-gray-100">
                @foreach($logs as $log)
                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-900">
                                To {{ ucfirst($log->recipient_type) }}
                                <span class="text-gray-400 font-normal">&middot; {{ $log->phone ?? 'no phone on file' }}</span>
                            </p>
                            <div class="flex items-center gap-2">
                                <x-status-badge :status="$log->status" />
                                <span class="text-xs text-gray-400">{{ $log->created_at->format('d M, g:i A') }}</span>
                            </div>
                        </div>
                        <p class="mt-1 text-sm text-gray-600 whitespace-pre-line">{{ $log->message }}</p>
                        @if($log->profile_link)
                            <a href="{{ $log->profile_link }}" target="_blank" class="text-xs text-primary-600 hover:text-primary-800">{{ $log->profile_link }}</a>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="p-4 border-t border-gray-100">{{ $logs->links() }}</div>
        @endif
    </x-card>
</x-app-layout>
