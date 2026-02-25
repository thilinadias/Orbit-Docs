<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\InteractsWithRelationships;
use App\Traits\LogsActivity;

class Document extends Model
{
    use HasFactory, InteractsWithRelationships, LogsActivity;

    protected $guarded = [];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function documentable()
    {
        return $this->morphTo();
    }

    public function versions()
    {
        return $this->hasMany(DocumentVersion::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class , 'taggable');
    }

    public function isImage()
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isPdf()
    {
        return $this->mime_type === 'application/pdf';
    }

    public function isWord()
    {
        return in_array($this->mime_type, [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/msword'
        ]);
    }
}
