<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApartmentInteraction extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'interacted_at' => 'datetime',
    ];

    public function apartament(): BelongsTo
    {
        return $this->belongsTo(Apartament::class);
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return [
            'contacted' => 'Contactat',
            'visit_requested' => 'Vizionare ceruta',
            'visit_scheduled' => 'Vizionare programata',
            'visited' => 'Vazut',
            'follow_up' => 'Follow-up',
            'offer' => 'Oferta',
        ][$this->type] ?? $this->type;
    }
}
