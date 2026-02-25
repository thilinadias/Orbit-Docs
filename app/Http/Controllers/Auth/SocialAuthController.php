<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Supported OAuth providers.
     */
    private array $providers = ['google', 'microsoft'];

    /**
     * Redirect the user to the OAuth provider.
     */
    public function redirect(string $provider)
    {
        if (!in_array($provider, $this->providers)) {
            abort(404, 'Unsupported provider.');
        }

        if (!$this->isProviderConfigured($provider)) {
            return redirect()->route('login')
                ->with('error', ucfirst($provider) . ' authentication is not configured.');
        }

        $this->applyProviderConfig($provider);

        $driver = Socialite::driver($provider);

        // Microsoft needs specific scopes
        if ($provider === 'microsoft') {
            $driver->scopes(['User.Read']);
        }

        return $driver->redirect();
    }

    /**
     * Handle the callback from the OAuth provider.
     */
    public function callback(string $provider)
    {
        if (!in_array($provider, $this->providers)) {
            abort(404, 'Unsupported provider.');
        }

        if (!$this->isProviderConfigured($provider)) {
            return redirect()->route('login')
                ->with('error', ucfirst($provider) . ' authentication is not configured.');
        }

        $this->applyProviderConfig($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
        }
        catch (\Throwable $e) {
            return redirect()->route('login')
                ->with('error', 'Authentication failed: ' . $e->getMessage());
        }

        // 1. Find by OAuth provider + ID (returning SSO user)
        $user = User::where('oauth_provider', $provider)
            ->where('oauth_id', $socialUser->getId())
            ->first();

        // 2. If not found, try matching by email (first-time SSO link)
        if (!$user) {
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // Link existing account to OAuth
                $user->update([
                    'oauth_provider' => $provider,
                    'oauth_id' => $socialUser->getId(),
                ]);
            }
        }

        // 3. If still not found, optionally auto-create
        if (!$user) {
            if (Setting::get('oauth_auto_create_users') !== '1') {
                return redirect()->route('login')
                    ->with('error', 'No account found for ' . $socialUser->getEmail() . '. Please contact your administrator.');
            }

            $user = User::create([
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'password' => null,
                'oauth_provider' => $provider,
                'oauth_id' => $socialUser->getId(),
                'email_verified_at' => now(),
            ]);
        }

        // 4. Check if account is active
        if (isset($user->status) && $user->status === 'suspended') {
            return redirect()->route('login')
                ->with('error', 'Your account has been suspended.');
        }

        // 5. Log in the user
        $user->update(['last_login_at' => now()]);
        Auth::login($user, true);
        request()->session()->regenerate();

        return redirect()->intended('/');
    }

    /**
     * Check if a provider has credentials configured in the settings.
     */
    private function isProviderConfigured(string $provider): bool
    {
        $clientId = Setting::get("oauth_{$provider}_client_id");
        $clientSecret = Setting::get("oauth_{$provider}_client_secret");

        return !empty($clientId) && !empty($clientSecret);
    }

    /**
     * Dynamically override config/services.php with database settings.
     */
    private function applyProviderConfig(string $provider): void
    {
        $clientId = Setting::get("oauth_{$provider}_client_id");
        $clientSecret = Setting::get("oauth_{$provider}_client_secret");

        config([
            "services.{$provider}.client_id" => $clientId,
            "services.{$provider}.client_secret" => $clientSecret,
            "services.{$provider}.redirect" => url("/auth/{$provider}/callback"),
        ]);

        // Microsoft needs a tenant ID
        if ($provider === 'microsoft') {
            $tenantId = Setting::get('oauth_microsoft_tenant_id', 'common');
            config(["services.microsoft.tenant" => $tenantId]);
        }
    }
}
