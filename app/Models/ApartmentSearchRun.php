<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApartmentSearchRun extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'observed_at' => 'datetime',
    ];

    public function search(): BelongsTo
    {
        return $this->belongsTo(ApartmentSearch::class, 'apartment_search_id');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(ApartmentListingSnapshot::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ApartmentListingEvent::class);
    }
}
