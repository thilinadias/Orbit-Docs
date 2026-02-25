<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        return view('settings');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'system_name' => 'nullable|string|max:255',
            'system_logo' => 'nullable|image|max:2048',
            'sidebar_color' => 'nullable|string|max:7',
            'oauth_google_client_id' => 'nullable|string|max:500',
            'oauth_google_client_secret' => 'nullable|string|max:500',
            'oauth_microsoft_client_id' => 'nullable|string|max:500',
            'oauth_microsoft_client_secret' => 'nullable|string|max:500',
            'oauth_microsoft_tenant_id' => 'nullable|string|max:255',
            'oauth_auto_create_users' => 'nullable|in:0,1',
        ]);

        // System personalization
        if ($request->hasFile('system_logo')) {
            $path = $request->file('system_logo')->store('branding', 'public');
            Setting::set('system_logo', $path);
        }

        if ($request->has('system_name')) {
            Setting::set('system_name', $request->system_name);
        }

        if ($request->has('sidebar_color')) {
            Setting::set('sidebar_color', $request->sidebar_color);
        }

        // OAuth / SSO settings
        $oauthKeys = [
            'oauth_google_client_id',
            'oauth_google_client_secret',
            'oauth_microsoft_client_id',
            'oauth_microsoft_client_secret',
            'oauth_microsoft_tenant_id',
            'oauth_auto_create_users',
        ];

        foreach ($oauthKeys as $key) {
            if ($request->has($key)) {
                Setting::set($key, $request->input($key));
            }
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
