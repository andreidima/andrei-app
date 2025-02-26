<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refrain extends Model
{
    use HasFactory;

    protected $table = 'refrains';
    protected $guarded = [];

    protected $casts = [
        'since' => 'datetime',
    ];

    public function path($action = 'show')
    {
        return match ($action) {
            'edit' => route('refrains.edit', $this->id),
            'destroy' => route('refrains.destroy', $this->id),
            default => route('refrains.show', $this->id),
        };
    }
}
