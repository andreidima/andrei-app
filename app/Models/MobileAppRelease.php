<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileAppRelease extends Model
{
    protected $fillable = [
        'platform',
        'version_code',
        'version_name',
        'apk_url',
        'release_notes',
        'required',
        'published_at',
    ];

    protected $casts = [
        'version_code' => 'integer',
        'required' => 'boolean',
        'published_at' => 'datetime',
    ];
}
