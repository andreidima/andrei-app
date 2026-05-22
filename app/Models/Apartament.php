<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apartament extends Model
{
    use HasFactory;

    protected $table = 'apartamente';
    protected $guarded = [];

    protected $casts = [
        'vizionare_at' => 'datetime',
        'pret' => 'integer',
        'cheltuieli_lunare' => 'integer',
        'costuri_extra_estimate' => 'integer',
        'suprafata_mp' => 'integer',
        'camere' => 'integer',
        'etaj' => 'integer',
        'are_lift' => 'boolean',
        'are_balcon' => 'boolean',
        'are_parcare' => 'boolean',
        'scor' => 'integer',
    ];

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
}
