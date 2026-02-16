<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Two-Factor Authentication') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Add additional security to your account using two-factor authentication.') }}
        </p>
    </header>

    @if (Auth::user()->hasTwoFactorEnabled())
        <div class="p-4 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 rounded-lg">
            {{ __('Two-Factor Authentication is currently ENABLED.') }}
        </div>

        @if(!Auth::user()->is_2fa_enforced || Auth::user()->is_super_admin || Auth::user()->hasRole('admin'))
            <form method="POST" action="{{ route('2fa.disable') }}" class="mt-4">
                @csrf
                
                <!-- Require password to disable -->
                <div>
                    <x-input-label for="password_disable_2fa" :value="__('Current Password')" />
                    <x-text-input id="password_disable_2fa" name="password" type="password" class="mt-1 block w-3/4" placeholder="{{ __('Password') }}" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <x-danger-button class="mt-4">
                    {{ __('Disable 2FA') }}
                </x-danger-button>
            </form>
        @else
            <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <span class="text-sm text-gray-600 dark:text-gray-400 font-medium">
                        {{ __('Two-Factor Authentication is enforced by your administrator.') }}
                    </span>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-500 ml-7">
                    {{ __('Please contact an administrator if you need to reset or disable it.') }}
                </p>
            </div>
        @endif
    @else
        <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400 rounded-lg mb-4">
            {{ __('Two-Factor Authentication is currently DISABLED.') }}
        </div>

        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            {{ __('When two-factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone\'s Google Authenticator application.') }}
        </p>

        <a href="{{ route('2fa.setup') }}">
            <x-primary-button>
                {{ __('Enable 2FA') }}
            </x-primary-button>
        </a>
    @endif
</section>
