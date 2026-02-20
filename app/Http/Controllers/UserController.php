<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Authorization: Manage Settings or Super Admin
        // This is a global user list.
        $this->authorize('user.view');

        $query = User::with(['organizations']);

        if ($request->has('organization_id') && $request->organization_id) {
            $query->whereHas('organizations', function ($q) use ($request) {
                $q->where('organizations.id', $request->organization_id);
            });
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();
        $organizations = Organization::orderBy('name')->get();

        return view('users.index', compact('users', 'organizations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('user.manage');
        $organizations = Organization::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        return view('users.create', compact('organizations', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('user.manage');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'status' => 'required|in:active,disabled',
            'primary_organization_id' => 'nullable|exists:organizations,id',
            'role_id' => 'nullable|exists:roles,id', // Initial role for primary org
            'is_2fa_enforced' => 'nullable', // Checkbox
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => $validated['status'],
            'is_2fa_enforced' => $request->has('is_2fa_enforced'),
        ]);

        if ($request->primary_organization_id) {
            $user->organizations()->attach($request->primary_organization_id, [
                'role_id' => $request->role_id,
                'is_primary' => true
            ]);
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
    //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->authorize('user.manage');
        $user = User::with('organizations')->findOrFail($id);
        $organizations = Organization::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        $permissions = \App\Models\Permission::all()->groupBy('module');

        return view('users.edit', compact('user', 'organizations', 'roles', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->authorize('user.manage');
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'status' => 'required|in:active,disabled',
            'password' => 'nullable|string|min:8|confirmed',
            'is_2fa_enforced' => 'nullable',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->status = $validated['status'];
        $user->is_2fa_enforced = $request->has('is_2fa_enforced');

        // Reset 2FA if requested
        if ($request->has('reset_2fa')) {
            $user->google2fa_secret = null;
            $user->two_factor_recovery_codes = null;
        // If we reset, they are no longer "enabled", so if enforced, they will be forced to setup again next login.
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // Handle organization assignment updates in a separate method or here if simple
        // For strict RBAC, specific org-role management might be better done via API/modal
        // But for update form, we can manage existing pivots?
        // Let's stick to user basics here. Org assignment usually separate or advanced.

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('user.manage');
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function attachOrganization(Request $request, User $user)
    {
        $this->authorize('user.manage');

        // Remove debug dump

        try {
            $validated = $request->validate([
                'organization_ids' => 'required|array|min:1',
                'organization_ids.*' => 'exists:organizations,id',
                'role_id' => 'required', // Can be 'custom' or int
                'permissions' => 'nullable|array',
                'permissions.*' => 'exists:permissions,slug',
            ]);

            $roleId = $validated['role_id'];
            $permissions = null;

            if ($roleId === 'custom') {
                $roleId = null; // No standard role
                $permissions = json_encode($request->input('permissions', []));
            }

            // Prepare pivot data
            $pivotData = [
                'role_id' => $roleId,
                'permissions' => $permissions
            ];

            $successCount = 0;
            $updateCount = 0;

            foreach ($validated['organization_ids'] as $orgId) {
                // Check explicitly in DB to avoid stale cache issues
                $exists = $user->organizations()->where('organization_id', $orgId)->exists();

                if ($exists) {
                    // Update existing
                    $user->organizations()->updateExistingPivot($orgId, $pivotData);
                    $updateCount++;
                }
                else {
                    // Attach new
                    $user->organizations()->attach($orgId, $pivotData);
                    $successCount++;
                }
            }

            $msg = "Successfully assigned to {$successCount} organization(s) and updated {$updateCount} existing assignments.";
            Log::info('Organization attachment successful', ['message' => $msg]);

            return redirect()->back()->with('success', $msg);
        }
        catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
        catch (\Exception $e) {
            Log::error('Organization attachment failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to assign: ' . $e->getMessage());
        }
    }

    public function detachOrganization(User $user, Organization $organization)
    {
        $this->authorize('user.manage');
        $user->organizations()->detach($organization->id);
        return redirect()->back()->with('success', 'Organization access removed.');
    }
}
