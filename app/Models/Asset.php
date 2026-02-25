<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Traits\InteractsWithRelationships;

use App\Traits\LogsActivity;

class Asset extends Model
{
    use HasFactory, InteractsWithRelationships, LogsActivity;

    protected $fillable = [
        'organization_id',
        'asset_type_id',
        'site_id',
        'name',
        'asset_tag',
        'serial_number',
        'manufacturer',
        'model',
        'purchase_date',
        'warranty_expiration_date',
        'end_of_life',
        'status',
        'assigned_to',
        'ip_address',
        'mac_address',
        'os_version',
        'monitoring_enabled',
        'rmm_agent_installed',
        'notes',
        'status',
    ];

    protected $guarded = [];

    const STATUS_ACTIVE = 'active';
    const STATUS_ARCHIVED = 'archived';
    const STATUS_BROKEN = 'broken';
    const STATUS_SUSPENDED = 'suspended';

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiration_date' => 'date',
        'end_of_life' => 'date',
        'monitoring_enabled' => 'boolean',
        'rmm_agent_installed' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function type()
    {
        return $this->belongsTo(AssetType::class , 'asset_type_id');
    }

    public function values()
    {
        return $this->hasMany(AssetValue::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class , 'documentable');
    }

    public function customFields()
    {
        return $this->hasManyThrough(AssetCustomField::class , AssetType::class , 'id', 'asset_type_id', 'asset_type_id', 'id');
    }
}
