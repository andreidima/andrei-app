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
        'suprafata_mp' => 'integer',
        'camere' => 'integer',
        'etaj' => 'integer',
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
            'astept_raspuns' => 'bg-warning text-dark',
            'respins' => 'bg-danger',
            'oferta' => 'bg-primary',
        ][$this->status] ?? 'bg-secondary';
    }
}
