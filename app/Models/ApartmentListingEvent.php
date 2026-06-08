<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApartmentListingEvent extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'occurred_at' => 'datetime',
        'is_reviewed' => 'boolean',
    ];

    public function search(): BelongsTo
    {
        return $this->belongsTo(ApartmentSearch::class, 'apartment_search_id');
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(ApartmentSearchRun::class, 'apartment_search_run_id');
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(ExternalApartmentListing::class, 'external_apartment_listing_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return [
            'new_listing' => 'Nou',
            'price_changed' => 'Pret schimbat',
            'under_option_added' => 'Optiune adaugata',
            'under_option_removed' => 'Optiune eliminata',
            'status_changed' => 'Status schimbat',
            'missing_from_search' => 'Lipseste din cautare',
            'reappeared' => 'Reaparut',
        ][$this->type] ?? $this->type;
    }

    public function getBadgeClassAttribute(): string
    {
        return [
            'new_listing' => 'bg-success',
            'price_changed' => 'bg-warning text-dark',
            'under_option_added' => 'bg-info text-dark',
            'under_option_removed' => 'bg-primary',
            'status_changed' => 'bg-secondary',
            'missing_from_search' => 'bg-danger',
            'reappeared' => 'bg-dark',
        ][$this->type] ?? 'bg-secondary';
    }
}
