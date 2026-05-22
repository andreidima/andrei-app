<?php

namespace App\Models\Apps;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feature extends Model
{
    use HasFactory;

    protected $table = 'apps_features';
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function path()
    {
        return "/apps/features/{$this->id}";
    }

    public function implementations(): HasMany
    {
        return $this->hasMany(FeatureImplementation::class, 'feature_id');
    }
}
