<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index(Request $request)
    {
        $organization = $request->attributes->get('current_organization');
        $sites = $organization->sites()->withCount('assets')->latest()->paginate(10);
        return view('sites.index', compact('organization', 'sites'));
    }

    public function create(Request $request)
    {
        $organization = $request->attributes->get('current_organization');
        return view('sites.create', compact('organization'));
    }

    public function store(Request $request)
    {
        $organization = $request->attributes->get('current_organization');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'site_manager' => 'nullable|string|max:255',
            'internet_provider' => 'nullable|string|max:255',
            'circuit_id' => 'nullable|string|max:255',
            'alarm_code' => 'nullable|string|max:255',
            'after_hours_access' => 'nullable|string',
            'timezone' => 'nullable|string|max:100',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('sites', 'public');
        }

        $organization->sites()->create($validated);

        return redirect()->route('sites.index', $organization->slug)->with('success', 'Site created.');
    }

    public function edit(Request $request, $organization, Site $site)
    {
        $currentOrganization = $request->attributes->get('current_organization');

        // Ensure site belongs to organization
        if ($site->organization_id !== $currentOrganization->id) {
            abort(404);
        }

        return view('sites.edit', compact('currentOrganization', 'site'));
    }

    public function show(Request $request, $organization, Site $site)
    {
        $currentOrganization = $request->attributes->get('current_organization');

        if ($site->organization_id !== $currentOrganization->id) {
            abort(404);
        }

        $currentOrganization->logActivity('view', $site, 'Viewed site');

        return view('sites.show', [
            'currentOrganization' => $currentOrganization,
            'site' => $site,
            'assets' => $site->assets()->with('type')->latest()->get(),
            'recent_activity' => $currentOrganization->activityLogs()
            ->where('subject_type', Site::class)
            ->where('subject_id', $site->id)
            ->with('user')
            ->latest()
            ->take(5)
            ->get(),
        ]);
    }

    public function update(Request $request, $organization, Site $site)
    {
        $currentOrganization = $request->attributes->get('current_organization');

        if ($site->organization_id !== $currentOrganization->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'site_manager' => 'nullable|string|max:255',
            'internet_provider' => 'nullable|string|max:255',
            'circuit_id' => 'nullable|string|max:255',
            'alarm_code' => 'nullable|string|max:255',
            'after_hours_access' => 'nullable|string',
            'timezone' => 'nullable|string|max:100',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('sites', 'public');
        }
        else {
            unset($validated['logo']);
        }

        $site->update($validated);

        return redirect()->route('sites.index', $currentOrganization->slug)->with('success', 'Site updated.');
    }

    public function destroy(Request $request, $organization, Site $site)
    {
        $currentOrganization = $request->attributes->get('current_organization');

        if ($site->organization_id !== $currentOrganization->id) {
            abort(404);
        }

        $site->delete();

        return redirect()->route('sites.index', $currentOrganization->slug)->with('success', 'Site deleted.');
    }

    public function updateNotes(Request $request, $organization, Site $site)
    {
        $currentOrganization = $request->attributes->get('current_organization');

        if ($site->organization_id !== $currentOrganization->id) {
            abort(404);
        }

        $site->update([
            'notes' => $request->input('notes')
        ]);

        return back()->with('success', 'Notes updated.');
    }

    public function suspend(Request $request, $organization, Site $site)
    {
        $currentOrganization = $request->attributes->get('current_organization');

        if ($site->organization_id !== $currentOrganization->id) {
            abort(404);
        }

        $site->suspend();

        return back()->with('success', 'Site suspended successfully.');
    }

    public function activate(Request $request, $organization, Site $site)
    {
        $currentOrganization = $request->attributes->get('current_organization');

        if ($site->organization_id !== $currentOrganization->id) {
            abort(404);
        }

        $site->activate();

        return back()->with('success', 'Site activated successfully.');
    }
}
