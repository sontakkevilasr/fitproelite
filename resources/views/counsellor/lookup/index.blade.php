<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Take a Call" subtitle="Enter the caller's phone number to pull up their history, or start a new enquiry." />
    </x-slot>

    @if(session('status'))
        <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3">{{ session('status') }}</div>
    @endif

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="My Clients" :value="$stats['myClients']" />
        <x-stat-card label="Follow-ups Due" :value="$stats['followUpsDue']" />
        <a href="{{ route('counsellor.clients.index') }}" class="block">
            <x-card class="h-full hover:border-primary-300 transition flex items-center justify-center text-center">
                <span class="text-sm font-medium text-primary-600">View My Clients →</span>
            </x-card>
        </a>
        <a href="{{ route('counsellor.follow-ups.index') }}" class="block">
            <x-card class="h-full hover:border-primary-300 transition flex items-center justify-center text-center">
                <span class="text-sm font-medium text-primary-600">View Follow-ups →</span>
            </x-card>
        </a>
    </div>

    <div data-search-url="{{ route('counsellor.lookup.search') }}" x-data="phoneLookup($el.dataset.searchUrl)" class="max-w-xl">
        <x-card>
            <label for="phone-search" class="block text-sm font-medium text-gray-700 mb-2">Caller's phone number</label>
            <div class="flex gap-2">
                <input
                    id="phone-search"
                    type="tel"
                    x-model="phone"
                    @input.debounce.500ms="search()"
                    @keydown.enter.prevent="search()"
                    placeholder="e.g. 9876543210"
                    autofocus
                    class="flex-1 border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-lg"
                >
                <x-secondary-button type="button" @click="search()">Search</x-secondary-button>
            </div>

            <p class="mt-2 text-sm text-gray-400" x-show="loading">Searching…</p>
            <p class="mt-2 text-sm text-gray-500" x-show="searched && found" x-cloak>
                Found <span x-text="result?.name"></span> — redirecting…
            </p>

            <div x-show="searched && !found" x-cloak class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-sm text-gray-600 mb-3">No existing client with this number. Capture a new enquiry:</p>

                <form method="POST" action="{{ route('counsellor.clients.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="phone" :value="phone">

                    <div>
                        <x-input-label for="name" value="Name" />
                        <x-text-input id="name" name="name" class="mt-1" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <x-input-label value="Phone" />
                            <div class="mt-1 border border-gray-200 bg-gray-50 rounded-md px-3 py-2 text-sm text-gray-600" x-text="phone"></div>
                        </div>
                        <div>
                            <x-input-label for="email" value="Email (optional)" />
                            <x-text-input id="email" type="email" name="email" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="address" value="Address" />
                        <x-text-input id="address" name="address" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <x-input-label for="package_id" value="Package interested in" />
                            <x-select-input id="package_id" name="package_id" class="mt-1">
                                <option value="">Not sure yet</option>
                                @foreach($packages as $package)
                                    <option value="{{ $package->id }}">{{ $package->name }}</option>
                                @endforeach
                            </x-select-input>
                        </div>
                        <div>
                            <x-input-label for="interest_level_id" value="How interested?" />
                            <x-select-input id="interest_level_id" name="interest_level_id" class="mt-1">
                                <option value="">—</option>
                                @foreach($interestLevels as $level)
                                    <option value="{{ $level->id }}">{{ $level->name }}</option>
                                @endforeach
                            </x-select-input>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="notes" value="Enquiry notes" />
                        <x-textarea-input id="notes" name="notes" rows="3" class="mt-1" placeholder="What did they ask about?"></x-textarea-input>
                    </div>

                    <x-primary-button>Save Enquiry</x-primary-button>
                </form>
            </div>
        </x-card>
    </div>

    @push('scripts')
    <script>
        function phoneLookup(searchUrl) {
            return {
                phone: '',
                loading: false,
                searched: false,
                found: false,
                result: null,
                search() {
                    if (this.phone.trim().length < 3) {
                        this.searched = false;
                        return;
                    }
                    this.loading = true;
                    fetch(searchUrl + '?phone=' + encodeURIComponent(this.phone))
                        .then((r) => r.json())
                        .then((data) => {
                            this.loading = false;
                            this.searched = true;
                            this.found = data.found;
                            this.result = data.client || null;
                            if (data.found) {
                                window.location = data.redirect;
                            }
                        });
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
