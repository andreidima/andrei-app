<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Apartament extends Model
{
    use HasFactory;

    protected $table = 'apartamente';
    protected $guarded = [];

    protected $casts = [
        'vizionare_at' => 'datetime',
        'pret' => 'integer',
        'pret_maxim_oferta' => 'integer',
        'cheltuieli_lunare' => 'integer',
        'costuri_extra_estimate' => 'integer',
        'venit_cadastral' => 'integer',
        'suprafata_mp' => 'integer',
        'camere' => 'integer',
        'bai' => 'integer',
        'toalete' => 'integer',
        'etaj' => 'integer',
        'an_constructie' => 'integer',
        'etaje_cladire' => 'integer',
        'peb_consum' => 'integer',
        'are_lift' => 'boolean',
        'are_balcon' => 'boolean',
        'are_parcare' => 'boolean',
        'electricitate_conforma' => 'boolean',
        'are_pivnita' => 'boolean',
        'disponibil_din' => 'date',
        'prioritate' => 'integer',
        'scor' => 'integer',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(ApartmentInteraction::class);
    }

    public function path()
    {
        return "/apartamente/{$this->id}";
    }

    public function getStatusLabelAttribute()
    {
        return [
            'de_vazut' => 'De vazut',
            'programat' => 'Programat',
            'vazut' => 'Vazut',
            'shortlist' => 'Shortlist',
            'de_revazut' => 'De revazut',
            'astept_raspuns' => 'Astept raspuns',
            'respins' => 'Respins',
            'oferta' => 'Oferta',
        ][$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute()
    {
        return [
            'de_vazut' => 'bg-secondary',
            'programat' => 'bg-info text-dark',
            'vazut' => 'bg-success',
            'shortlist' => 'bg-primary',
            'de_revazut' => 'bg-dark',
            'astept_raspuns' => 'bg-warning text-dark',
            'respins' => 'bg-danger',
            'oferta' => 'bg-primary',
        ][$this->status] ?? 'bg-secondary';
    }

    public function getDecisionLabelAttribute()
    {
        return [
            'nu' => 'Nu',
            'poate' => 'Poate',
            'shortlist' => 'Shortlist',
            'candidat_oferta' => 'Candidat oferta',
        ][$this->decizie] ?? $this->decizie;
    }

    public function getAgentContactAttribute(): ?string
    {
        return $this->agent?->display_contact ?: $this->contact;
    }
}
