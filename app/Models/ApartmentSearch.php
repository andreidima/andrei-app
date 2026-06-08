<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApartmentSearch extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'last_checked_at' => 'datetime',
    ];

    public function runs(): HasMany
    {
        return $this->hasMany(ApartmentSearchRun::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ApartmentListingEvent::class);
    }
}
