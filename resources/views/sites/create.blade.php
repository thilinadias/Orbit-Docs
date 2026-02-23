<x-app-layout>
    <x-slot name="header">
        Create Site
    </x-slot>

    <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <form action="{{ route('sites.store', $organization->slug) }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="grid gap-6 mb-4 md:grid-cols-2">
                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Site Name</span>
                    <input name="name" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="Main Office" />
                </label>
                
                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Site Logo</span>
                    <input type="file" name="logo" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" />
                </label>
            </div>

            <div class="mb-4">
                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Address</span>
                    <input name="address" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="123 Main St" />
                </label>
            </div>

            <div class="grid gap-6 mb-4 md:grid-cols-3">
                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">City</span>
                    <input name="city" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="New York" />
                </label>
                
                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">State</span>
                    <input name="state" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="NY" />
                </label>

                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Postcode</span>
                    <input name="postcode" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="10001" />
                </label>
            </div>

            {{-- Location Details --}}
            <div class="mt-6 mb-4">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 border-b border-gray-200 dark:border-gray-700 pb-2">Location Details</h4>
                <div class="grid gap-6 mb-4 md:grid-cols-2">
                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Site Manager</span>
                        <input name="site_manager" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="John Smith" />
                    </label>

                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Timezone</span>
                        <input name="timezone" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="e.g. Asia/Colombo" />
                    </label>
                </div>
            </div>

            {{-- Network & Access --}}
            <div class="mb-4">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 border-b border-gray-200 dark:border-gray-700 pb-2">Network & Access</h4>
                <div class="grid gap-6 mb-4 md:grid-cols-2">
                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Internet Provider</span>
                        <input name="internet_provider" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="ISP Name" />
                    </label>

                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Circuit ID</span>
                        <input name="circuit_id" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" />
                    </label>

                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Alarm Code</span>
                        <input name="alarm_code" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" />
                    </label>

                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">After Hours Access</span>
                        <input name="after_hours_access" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" />
                    </label>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">Notes</span>
                    <textarea name="notes" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-textarea focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray" rows="3"></textarea>
                </label>
            </div>

            <div class="mt-4">
                <button type="submit" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                    Create Site
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
