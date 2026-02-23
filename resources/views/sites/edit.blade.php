<x-app-layout>
    <x-slot name="header">
        Edit Site
    </x-slot>

    <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <form action="{{ route('sites.update', [$currentOrganization->slug, $site->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="grid gap-6 mb-4 md:grid-cols-2">
                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Site Name</span>
                    <input name="name" value="{{ $site->name }}" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" />
                </label>
                
                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Site Logo</span>
                    <input type="file" name="logo" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" />
                </label>
            </div>

            @if($site->logo)
                <div class="mb-4">
                    <img src="{{ asset('storage/' . $site->logo) }}" alt="Current Site Logo" class="h-12 w-auto bg-gray-100 p-1 rounded">
                </div>
            @endif

            <div class="mb-4">
                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Address</span>
                    <input name="address" value="{{ $site->address }}" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" />
                </label>
            </div>

            <div class="grid gap-6 mb-4 md:grid-cols-3">
                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">City</span>
                    <input name="city" value="{{ $site->city }}" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" />
                </label>
                
                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">State</span>
                    <input name="state" value="{{ $site->state }}" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" />
                </label>

                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Postcode</span>
                    <input name="postcode" value="{{ $site->postcode }}" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" />
                </label>
            </div>

            {{-- Location Details --}}
            <div class="mt-6 mb-4">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 border-b border-gray-200 dark:border-gray-700 pb-2">Location Details</h4>
                <div class="grid gap-6 mb-4 md:grid-cols-2">
                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Site Manager</span>
                        <input name="site_manager" value="{{ $site->site_manager }}" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" />
                    </label>

                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Timezone</span>
                        <input name="timezone" value="{{ $site->timezone }}" placeholder="e.g. Asia/Colombo" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" />
                    </label>
                </div>
            </div>

            {{-- Network & Access --}}
            <div class="mb-4">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 border-b border-gray-200 dark:border-gray-700 pb-2">Network & Access</h4>
                <div class="grid gap-6 mb-4 md:grid-cols-2">
                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Internet Provider</span>
                        <input name="internet_provider" value="{{ $site->internet_provider }}" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" />
                    </label>

                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Circuit ID</span>
                        <input name="circuit_id" value="{{ $site->circuit_id }}" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" />
                    </label>

                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Alarm Code</span>
                        <input name="alarm_code" value="{{ $site->alarm_code }}" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" />
                    </label>

                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">After Hours Access</span>
                        <input name="after_hours_access" value="{{ $site->after_hours_access }}" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" />
                    </label>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Notes</span>
                    <textarea name="notes" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-textarea focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray" rows="3">{{ $site->notes }}</textarea>
                </label>
            </div>

            <div class="mt-4">
                <button type="submit" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                    Update Site
                </button>
            </div>
        </form>

        @can('suspend-organization')
        <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
            <h4 class="mb-4 text-lg font-semibold text-gray-600 dark:text-gray-300">Danger Zone</h4>
            
            @if($site->isSuspended())
                <form action="{{ route('sites.activate', [$currentOrganization->slug, $site->id]) }}" method="POST" class="inline-block">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-green-600 border border-transparent rounded-lg active:bg-green-600 hover:bg-green-700 focus:outline-none focus:shadow-outline-green">
                        Activate Site
                    </button>
                </form>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    This site is currently <strong>suspended</strong>. Activating it will restore active status for assets that were suspended.
                </p>
            @else
                <form action="{{ route('sites.suspend', [$currentOrganization->slug, $site->id]) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to suspend this site? This will suspend all related assets.');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-red-600 border border-transparent rounded-lg active:bg-red-600 hover:bg-red-700 focus:outline-none focus:shadow-outline-red">
                        Suspend Site
                    </button>
                </form>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Suspending this site will also suspend all its **Assets**.
                </p>
            @endif
        </div>
        @endcan
    </div>
</x-app-layout>
