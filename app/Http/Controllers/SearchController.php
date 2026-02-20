<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\Asset;
use App\Models\Document;
use App\Models\Credential;
use App\Models\Contact;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->get('q');

        if (empty($query)) {
            return response()->json([]);
        }

        $user = Auth::user();
        if (!$user)
            return response()->json([]);

        $isAdmin = false;
        try {
            if (method_exists($user, 'hasRole')) {
                $isAdmin = $user->hasRole('Super Admin');
            }
        }
        catch (\Exception $e) {
            Log::error('Role check failed', ['error' => $e->getMessage()]);
        }

        // Fallback: If user is email 'admin@example.com' or has specific role string/ID
        // (Adjust this based on actual app logic if known, otherwise isAdmin stays false)

        // If not super admin, restrict to their organizations
        $orgIds = $isAdmin ? null : $user->organizations->pluck('id');

        $results = [];

        // Search Organizations
        $orgQuery = Organization::where('name', 'LIKE', "%{$query}%");
        if (!$isAdmin) {
            $orgQuery->whereIn('id', $orgIds);
        }
        $organizations = $orgQuery->take(5)->get();

        foreach ($organizations as $org) {
            $results[] = [
                'id' => $org->id,
                'title' => $org->name,
                'subtitle' => 'Global Organization',
                'type' => 'Organization',
                'url' => route('dashboard', ['organization' => $org->slug]),
                'icon' => 'office-building',
                'preview' => [
                    'Name' => $org->name,
                    'Slug' => $org->slug,
                    'ID' => '#' . $org->id,
                    'Assets' => $org->assets()->count() . ' items',
                ]
            ];
        }

        // Search Assets
        $assetQuery = Asset::where(function ($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
                ->orWhere('serial_number', 'LIKE', "%{$query}%")
                ->orWhere('asset_tag', 'LIKE', "%{$query}%")
                ->orWhere('ip_address', 'LIKE', "%{$query}%")
                ->orWhere('manufacturer', 'LIKE', "%{$query}%")
                ->orWhere('model', 'LIKE', "%{$query}%");
        });

        if (!$isAdmin) {
            $assetQuery->whereIn('organization_id', $orgIds);
        }

        $assets = $assetQuery->with(['type', 'organization'])->take(8)->get();

        foreach ($assets as $asset) {
            $results[] = [
                'id' => $asset->id,
                'title' => $asset->name,
                'subtitle' => $asset->organization->name ?? 'Global',
                'type' => $asset->type->name ?? 'Asset',
                'url' => route('assets.show', ['organization' => $asset->organization->slug, 'asset' => $asset->id]),
                'icon' => 'cpu',
                'preview' => [
                    'Status' => strtoupper($asset->status ?? 'Active'),
                    'Serial' => $asset->serial_number ?? 'N/A',
                    'Tag' => $asset->asset_tag ?? 'N/A',
                    'IP' => $asset->ip_address ?? 'N/A',
                    'Model' => ($asset->manufacturer ? $asset->manufacturer . ' ' : '') . ($asset->model ?? ''),
                ]
            ];
        }

        // Search Documents
        $docQuery = Document::where('title', 'LIKE', "%{$query}%");
        if (!$isAdmin) {
            $docQuery->whereIn('organization_id', $orgIds);
        }

        $documents = $docQuery->with('organization')->take(5)->get();

        foreach ($documents as $doc) {
            $results[] = [
                'id' => $doc->id,
                'title' => $doc->title,
                'subtitle' => $doc->organization->name ?? 'Global',
                'type' => 'Document',
                'url' => route('documents.show', ['organization' => $doc->organization->slug, 'document' => $doc->id]),
                'icon' => 'document-text',
                'preview' => [
                    'Title' => $doc->title,
                    'Last Updated' => $doc->updated_at->diffForHumans(),
                ]
            ];
        }

        // Search Credentials
        // FIX: Using 'title' instead of 'name'
        $credQuery = Credential::where(function ($q) use ($query) {
            $q->where('title', 'LIKE', "%{$query}%")
                ->orWhere('username', 'LIKE', "%{$query}%");
        });

        if (!$isAdmin) {
            $credQuery->whereIn('organization_id', $orgIds);
        }

        $credentials = $credQuery->with('organization')->take(5)->get();

        foreach ($credentials as $cred) {
            $results[] = [
                'id' => $cred->id,
                'title' => $cred->title,
                'subtitle' => $cred->organization->name ?? 'Global',
                'type' => 'Credential',
                'url' => route('credentials.show', ['organization' => $cred->organization->slug, 'credential' => $cred->id]),
                'icon' => 'key',
                'preview' => [
                    'Title' => $cred->title,
                    'Username' => $cred->username,
                    'URL' => $cred->url ?? 'N/A',
                ]
            ];
        }

        // Search Contacts
        $contactQuery = Contact::where(function ($q) use ($query) {
            $q->where('first_name', 'LIKE', "%{$query}%")
                ->orWhere('last_name', 'LIKE', "%{$query}%")
                ->orWhere('email', 'LIKE', "%{$query}%");
        });

        if (!$isAdmin) {
            $contactQuery->whereIn('organization_id', $orgIds);
        }

        $contacts = $contactQuery->with('organization')->take(5)->get();

        foreach ($contacts as $contact) {
            $results[] = [
                'id' => $contact->id,
                'title' => $contact->first_name . ' ' . $contact->last_name,
                'subtitle' => $contact->organization->name ?? 'Global',
                'type' => 'Contact',
                'url' => route('contacts.show', ['organization' => $contact->organization->slug, 'contact' => $contact->id]),
                'icon' => 'user',
                'preview' => [
                    'Name' => $contact->first_name . ' ' . $contact->last_name,
                    'Email' => $contact->email,
                    'Phone' => $contact->phone_mobile ?? $contact->phone_office ?? 'N/A',
                ]
            ];
        }

        return response()->json($results);
    }
}
