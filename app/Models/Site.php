<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\LogsActivity;

class Site extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'organization_id',
        'name',
        'address',
        'city',
        'state',
        'postcode',
        'notes',
        'logo',
        'status',
        'site_manager',
        'internet_provider',
        'circuit_id',
        'alarm_code',
        'after_hours_access',
        'timezone',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
    public function favorites()
    {
        return $this->morphMany(Favorite::class , 'favoritable');
    }

    public function isSuspended()
    {
        return $this->status === 'suspended';
    }

    public function suspend()
    {
        $this->update(['status' => 'suspended']);

        // Cascade to Assets - Mark as 'suspended'
        $this->assets()->update(['status' => 'suspended']);
    }

    public function activate()
    {
        $this->update(['status' => 'active']);

        // Reactivate assets that were suspended
        $this->assets()->where('status', 'suspended')->update(['status' => 'active']);
    }
}
