<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExternalApartmentListing extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'latest_under_option' => 'boolean',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function snapshots(): HasMany
    {
        return $this->hasMany(ApartmentListingSnapshot::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ApartmentListingEvent::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->title ?: $this->url ?: ('Listing #' . $this->id);
    }
}
