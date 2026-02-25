<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // 1. Super Admin Bypass
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });

        // 2. Define Dynamic Gates
        // Instead of fetching all from DB at boot (which fails in tests),
        // we use a dynamic check if the ability looks like our slug pattern.
        \Illuminate\Support\Facades\Gate::after(function ($user, $ability, $result) {
            if ($result === true)
                return true;

            // Check if ability matches our permission slug pattern (e.g., document.view)
            if (str_contains($ability, '.')) {
                return $user->hasPermission($ability, request()->attributes->get('current_organization'));
            }

            return $result;
        });
    }
}
