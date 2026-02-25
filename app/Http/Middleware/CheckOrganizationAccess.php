<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Organization;

class CheckOrganizationAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $organizationSlug = $request->route('organization');

        if (!$organizationSlug) {
            return $next($request);
        }

        // If organization is passed as a model binding, getting the model.
        if ($organizationSlug instanceof Organization) {
            $organization = $organizationSlug;
        }
        else {
            $organization = Organization::where('slug', $organizationSlug)->firstOrFail();
        }

        // Check if user belongs to this organization (or is Super Admin)
        $user = $request->user();

        if ($user && ($user->organizations->contains($organization->id) || $user->hasRole('super-admin'))) {
            // Share current organization with views/controllers
            $request->attributes->set('current_organization', $organization);
            // Also share to views
            view()->share('currentOrganization', $organization);

            return $next($request);
        }

        abort(403, 'Unauthorized access to this organization.');
    }
}
