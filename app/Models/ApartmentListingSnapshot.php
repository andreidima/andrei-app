<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApartmentListingSnapshot extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'under_option' => 'boolean',
        'raw_payload' => 'array',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(ApartmentSearchRun::class, 'apartment_search_run_id');
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(ExternalApartmentListing::class, 'external_apartment_listing_id');
    }
}
