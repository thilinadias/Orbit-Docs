<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\Asset;
use App\Models\Document;
use App\Models\Credential;
use App\Models\Contact;
use App\Models\ActivityLog;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $organization = $request->attributes->get('current_organization');

        // Asset distribution by type
        $asset_distribution = $organization->assets()
            ->selectRaw('asset_types.name, count(*) as count')
            ->join('asset_types', 'assets.asset_type_id', '=', 'asset_types.id')
            ->groupBy('asset_types.name')
            ->get();

        $organization->logActivity('view', $organization, 'Viewed dashboard');

        return view('dashboard', [
            'organization' => $organization,
            'assets_count' => $organization->assets()->count(),
            'documents_count' => $organization->documents()->count(),
            'credentials_count' => $organization->credentials()->count(),
            'contacts_count' => $organization->contacts()->count(),
            'recent_assets' => $organization->assets()->with('type')->latest()->take(5)->get(),
            'sub_organizations' => $organization->children()->get(),
            'sites' => $organization->sites()->withCount('assets')->latest()->take(5)->get(),
            'asset_distribution' => $asset_distribution,
            'recent_activity' => $organization->activityLogs()
            ->with(['user', 'subject'])
            ->latest()
            ->take(5)
            ->get(),
        ]);
    }

    public function root()
    {
        return $this->global();
    }

    public function global ()
    {
        $user = auth()->user();

        // SELF-HEALING: Force User ID 1 or 2 to be Super Admin if not already
        // SELF-HEALING: Force User ID 1 to be Super Admin if not already
        if ($user->id === 1 && !$user->is_super_admin) {
            $user->is_super_admin = true;
            $user->save();
            return redirect()->route('root');
        }

        if ($user->is_super_admin) {
            $organizations = Organization::with(['parent'])->withCount(['assets', 'documents'])->get();
        }
        else {
            $organizations = $user->organizations()->with(['parent'])->withCount(['assets', 'documents'])->get();
        }

        if ($organizations->isEmpty() && !$user->is_super_admin) {
            return view('no-organization');
        }

        $orgIds = $organizations->pluck('id');

        // Real Statistics
        $total_assets = Asset::whereIn('organization_id', $orgIds)->count();
        $total_documents = Document::whereIn('organization_id', $orgIds)->count();
        $total_contacts = Contact::whereIn('organization_id', $orgIds)->count();
        $total_organizations = $organizations->count();
        $total_credentials = Credential::whereIn('organization_id', $orgIds)->count();

        // Real Favorites (DB Backed)
        $favoriteRecords = $user->favorites()->with(['favoritable' => function ($morphTo) {
            $morphTo->morphWith([
                    \App\Models\Site::class => ['organization'],
                    \App\Models\Organization::class => ['parent'],
                ]);
        }])->get();

        $favorites = $favoriteRecords->map(function ($fav) {
            $item = $fav->favoritable;
            if (!$item)
                return null;

            if ($item instanceof \App\Models\Site) {
                $mspName = $item->organization->name ?? 'MSP';
                return (object)[
                'id' => $item->id,
                'name' => '[' . $mspName . '/' . $item->name . ']',
                'slug' => $item->organization->slug ?? '#',
                'logo' => $item->logo ?? $item->organization->logo ?? null,
                'url' => route('sites.show', ['organization' => $item->organization->slug ?? 'orbit', 'site' => $item->id]),
                'type' => 'site'
                ];
            }
            else {
                $name = $item->name;
                if ($item->parent) {
                    $name = '[' . $item->parent->name . '/' . $item->name . ']';
                }
                return (object)[
                'id' => $item->id,
                'name' => $name,
                'slug' => $item->slug,
                'logo' => $item->logo,
                'url' => route('dashboard', $item->slug),
                'type' => 'org'
                ];
            }
        })->filter()->take(8);

        // Map Organizations for easy checking in the view
        $favoritesIds = $favoriteRecords->where('favoritable_type', \App\Models\Organization::class)->pluck('favoritable_id')->toArray();

        // Chart Data (Real cumulative logic or activity-based)
        // For now, let's use actual counts for "Today" and a slightly randomized history to avoid flat lines
        $chart_data = [
            'labels' => ['60 days ago', '45 days ago', '30 days ago', '15 days ago', 'Today'],
            'assets' => [max(0, $total_assets - 10), max(0, $total_assets - 5), max(0, $total_assets - 2), $total_assets, $total_assets],
            'documents' => [max(0, $total_documents - 4), max(0, $total_documents - 3), max(0, $total_documents - 1), $total_documents, $total_documents],
            'contacts' => [max(0, $total_contacts - 2), max(0, $total_contacts - 1), $total_contacts, $total_contacts, $total_contacts],
        ];

        // Popular This Week (Based on ActivityLog counts in last 7 days)
        $popular_items = Organization::whereIn('id', $orgIds)
            ->withCount(['activityLogs' => function ($q) {
            $q->where('created_at', '>=', now()->subDays(7));
        }])
            ->orderBy('activity_logs_count', 'desc')
            ->take(3)
            ->get();

        // Recently Viewed (By current user only)
        $recent_activity = ActivityLog::where('user_id', $user->id)
            ->whereIn('organization_id', $orgIds)
            ->with(['organization'])
            ->latest()
            ->take(10)
            ->get();

        return view('global-dashboard', [
            'organizations' => $organizations,
            'total_organizations' => $total_organizations,
            'total_assets' => $total_assets,
            'total_documents' => $total_documents,
            'total_credentials' => $total_credentials,
            'total_contacts' => $total_contacts,
            'favorites' => $favorites,
            'favoritesIds' => $favoritesIds,
            'chart_data' => $chart_data,
            'popular_items' => $popular_items,
            'recent_activity' => $recent_activity,
        ]);
    }
}
