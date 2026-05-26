<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function apartamente(): HasMany
    {
        return $this->hasMany(Apartament::class);
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(ApartmentInteraction::class);
    }

    public function getDisplayContactAttribute(): string
    {
        return collect([$this->name, $this->email, $this->phone])
            ->filter()
            ->implode(' / ');
    }
}
