<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Traits\LogsActivity;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'mfa_enabled',
        'last_login_at',
        'google2fa_secret',
        'is_2fa_enforced',
        'two_factor_recovery_codes',
        'is_super_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'mfa_secret',
        'google2fa_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_super_admin' => 'boolean',
        'mfa_enabled' => 'boolean',
        'is_2fa_enforced' => 'boolean',
        'last_login_at' => 'datetime',
        'permissions' => 'array', // Although this is on pivot, good practice if accessed directly
    ];

    public function organizations()
    {
        return $this->belongsToMany(Organization::class)
                    ->withPivot('role_id', 'is_primary', 'permissions')
                    ->withTimestamps();
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class)->withTimestamps();
    }

    // Helper to get role for current organization
    public function role(Organization $organization)
    {
        $pivot = $this->organizations()->where('organization_id', $organization->id)->first()?->pivot;
        if (!$pivot || !$pivot->role_id) return null;
        return Role::find($pivot->role_id);
    }
    
    /**
     * Check if user has a specific role.
     * 
     * @param string $roleSlug
     * @param Organization|null $organization
     * @return bool
     */
    public function hasRole($roleSlug, $organization = null)
    {
        // 0. Super Admin Bypass
        if ($this->is_super_admin) {
            return true;
        }

        // 1. Check strict cache/relationship
        // If organization is provided, check that specific pivot.
        if ($organization) {
            $role = $this->role($organization);
            return $role && $role->slug === $roleSlug;
        }

        // 2. Global check (if checking generic 'hasRole')
        // Check if user has this role in ANY organization
        return $this->organizations->pluck('pivot.role_id')->map(function($id) {
            return Role::find($id)?->slug;
        })->contains($roleSlug);
    }

    /**
     * Check if user has permission.
     * 
     * @param string $permissionSlug
     * @param Organization|null $organization
     * @return bool
     */
    public function hasPermission($permissionSlug, Organization $organization = null)
    {
        // 1. Super Admin bypass
        if ($this->is_super_admin) {
            return true;
        }

        // 2. Organization Scope - Check ALL organizations if none specified
        if (!$organization) {
            foreach ($this->organizations as $org) {
                // Use helper with loaded org to leverage keys
                if ($this->checkOrgPermission($org, $permissionSlug)) {
                    return true;
                }
            }
            return false;
        }

        return $this->checkOrgPermission($organization, $permissionSlug);
    }

    /**
     * Helper to check permission on a specific organization instance.
     * Uses eager-loaded pivot if available to avoid N+1 queries.
     */
    protected function checkOrgPermission($organization, $permissionSlug)
    {
        // Try to use loaded pivot; fallback to query if missing
        $pivot = $organization->pivot;
        if (!$pivot) {
             $pivot = $this->organizations()->where('organization_id', $organization->id)->first()?->pivot;
        }

        if (!$pivot) return false;

        // 3. Custom Permission Override
        if (!empty($pivot->permissions)) {
            $customPermissions = is_string($pivot->permissions) ? json_decode($pivot->permissions, true) : $pivot->permissions;
            return in_array($permissionSlug, $customPermissions ?? []);
        }

        // 4. Role Check
        if (!$pivot->role_id) return false;
        
        // optimization: Simple Role::find is cached by Laravel's internal identity map within a request? 
        // No, but it's fast by PK.
        $role = Role::find($pivot->role_id);
        if (!$role) return false;

        // 5. Check Role Permissions
        // Ideally we'd cache this too, but for now specific query is safer than loading all
        return $role->permissions()->where('slug', $permissionSlug)->exists();
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function apiTokens()
    {
        return $this->hasMany(ApiToken::class);
    }

    /**
     * Check if the user has enabled Two-Factor Authentication.
     *
     * @return bool
     */
    public function hasTwoFactorEnabled()
    {
        return !is_null($this->google2fa_secret);
    }

    public function requiresTwoFactorSetup()
    {
        return $this->is_2fa_enforced && !$this->hasTwoFactorEnabled();
    }

    public function logActivity($action, $description = null, $properties = null)
    {
        $data = [
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => self::class,
            'subject_id' => $this->id,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'log_name' => 'user',
        ];

        // If user belongs to an organization context (primary?), we could add organization_id
        // But users are global. If the action is in context of an org, the caller handles it.
        // For 2FA, it's personal.

        ActivityLog::create($data);
    }
}
