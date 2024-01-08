<?php

namespace App\Models\Apps;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pontaj extends Model
{
    use HasFactory;

    protected $table = 'apps_pontaje';
    protected $guarded = [];

    public function path()
    {
        return "/apps/pontaje/{$this->id}";
    }

    /**
     * Get the actualizare that owns the Pontaj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function actualizare(): BelongsTo
    {
        return $this->belongsTo(Actualizare::class, 'actualizare_id');
    }
}
