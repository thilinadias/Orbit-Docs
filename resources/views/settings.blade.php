<x-app-layout>
    <x-slot name="header">
        System Settings
    </x-slot>

    {{-- Success / Error Messages --}}
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- SECTION 1: System Personalization --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800 mb-6">
        <h3 class="mb-4 text-lg font-semibold text-gray-700 dark:text-gray-200">
            System Personalization
        </h3>

        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="grid gap-6 md:grid-cols-2">
                <!-- System Name -->
                <div class="mb-4">
                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">System Name</span>
                        <input name="system_name" value="{{ \App\Models\Setting::get('system_name', 'OrbitDocs') }}" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray" placeholder="OrbitDocs" />
                    </label>
                </div>

                <!-- Sidebar Color -->
                <div class="mb-4">
                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Sidebar Highlight Color</span>
                        <input type="color" name="sidebar_color" value="{{ \App\Models\Setting::get('sidebar_color', '#7e3af2') }}" class="block w-full mt-1 h-10 rounded-md border-gray-600 dark:bg-gray-700" />
                    </label>
                </div>

                <!-- System Logo -->
                <div class="mb-4 md:col-span-2">
                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">System Logo</span>
                        <input type="file" name="system_logo" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray" />
                    </label>
                    @if($logo = \App\Models\Setting::get('system_logo'))
                        <div class="mt-2">
                            <p class="text-xs text-gray-500 mb-1">Current Logo:</p>
                            <img src="{{ asset('storage/' . $logo) }}" alt="Logo" class="h-12 w-auto bg-gray-100 p-2 rounded">
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                    Save Settings
                </button>
            </div>
        </form>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- SECTION 2: Authentication / SSO --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <h3 class="mb-1 text-lg font-semibold text-gray-700 dark:text-gray-200">
            Authentication / SSO
        </h3>
        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
            Configure Single Sign-On with Microsoft 365 or Google. Legacy email/password login is always available.
        </p>

        <form action="{{ route('settings.update') }}" method="POST">
            @csrf

            {{-- Google OAuth --}}
            <div class="mb-6 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                <div class="flex items-center gap-3 mb-4">
                    <svg class="w-6 h-6" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                    <h4 class="text-md font-semibold text-gray-700 dark:text-gray-200">Google</h4>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm">
                            <span class="text-gray-700 dark:text-gray-400">Client ID</span>
                            <input name="oauth_google_client_id" value="{{ \App\Models\Setting::get('oauth_google_client_id') }}" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray" placeholder="xxxx.apps.googleusercontent.com" />
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm">
                            <span class="text-gray-700 dark:text-gray-400">Client Secret</span>
                            <input type="password" name="oauth_google_client_secret" value="{{ \App\Models\Setting::get('oauth_google_client_secret') }}" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray" placeholder="••••••••" />
                        </label>
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-500">
                    Callback URL: <code class="bg-gray-100 dark:bg-gray-900 px-1 rounded">{{ url('/auth/google/callback') }}</code>
                </p>
            </div>

            {{-- Microsoft OAuth --}}
            <div class="mb-6 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                <div class="flex items-center gap-3 mb-4">
                    <svg class="w-6 h-6" viewBox="0 0 23 23" fill="none"><path fill="#f35325" d="M1 1h10v10H1z"/><path fill="#81bc06" d="M12 1h10v10H12z"/><path fill="#05a6f0" d="M1 12h10v10H1z"/><path fill="#ffba08" d="M12 12h10v10H12z"/></svg>
                    <h4 class="text-md font-semibold text-gray-700 dark:text-gray-200">Microsoft 365</h4>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm">
                            <span class="text-gray-700 dark:text-gray-400">Application (Client) ID</span>
                            <input name="oauth_microsoft_client_id" value="{{ \App\Models\Setting::get('oauth_microsoft_client_id') }}" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray" placeholder="00000000-0000-0000-0000-000000000000" />
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm">
                            <span class="text-gray-700 dark:text-gray-400">Client Secret</span>
                            <input type="password" name="oauth_microsoft_client_secret" value="{{ \App\Models\Setting::get('oauth_microsoft_client_secret') }}" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray" placeholder="••••••••" />
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm">
                            <span class="text-gray-700 dark:text-gray-400">Tenant ID</span>
                            <input name="oauth_microsoft_tenant_id" value="{{ \App\Models\Setting::get('oauth_microsoft_tenant_id', 'common') }}" class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray" placeholder="common" />
                        </label>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">Use "common" for multi-tenant, or a specific tenant GUID.</p>
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-500">
                    Callback URL: <code class="bg-gray-100 dark:bg-gray-900 px-1 rounded">{{ url('/auth/microsoft/callback') }}</code>
                </p>
            </div>

            {{-- Auto-create toggle --}}
            <div class="mb-6 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="oauth_auto_create_users" value="0">
                    <input type="checkbox" name="oauth_auto_create_users" value="1"
                        {{ \App\Models\Setting::get('oauth_auto_create_users') === '1' ? 'checked' : '' }}
                        class="rounded border-gray-300 dark:border-gray-600 text-purple-600 shadow-sm focus:ring-purple-500 dark:focus:ring-purple-600 dark:focus:ring-offset-gray-800 dark:bg-gray-700">
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Auto-create users on SSO login</span>
                        <p class="text-xs text-gray-500 dark:text-gray-500">When disabled, only users whose email already exists in the system can sign in via SSO.</p>
                    </div>
                </label>
            </div>

            <div class="mt-6">
                <button type="submit" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                    Save Authentication Settings
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
