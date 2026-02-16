<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstalled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If the installed file exists, the app is installed.
        // We check for specific 'install' routes to prevent infinite loops if installed users try to access installer.
        // But for now, let's focus on the "Not Installed" case.
        // Bypass check for testing environment
        if (app()->runningUnitTests()) {
            return $next($request);
        }

        if (file_exists(storage_path('app/installed'))) {
            // App is installed.
            // If user tries to access /install routes, redirect to dashboard (unless we want to allow re-install? generally no).
            if ($request->is('install*')) {
                return redirect()->route('dashboard');
            }
            return $next($request);
        }

        // App is NOT installed.
        // Allow access to install/* routes
        if ($request->is('install*')) {
            return $next($request);
        }

        // Redirect everything else to the installer welcome page
        return redirect()->route('install.welcome');
    }
}
