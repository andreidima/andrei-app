<?php

namespace App\Models\Apps;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureImplementation extends Model
{
    use HasFactory;

    protected $table = 'apps_feature_implementations';
    protected $guarded = [];

    protected $casts = [
        'implemented_at' => 'date',
        'production_updated_at' => 'date',
    ];

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class, 'feature_id');
    }

    public function aplicatie(): BelongsTo
    {
        return $this->belongsTo(Aplicatie::class, 'aplicatie_id');
    }

    public function getStatusLabelAttribute()
    {
        return self::statusOptions()[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute()
    {
        return [
            'not_started' => 'bg-secondary',
            'planned' => 'bg-info text-dark',
            'in_progress' => 'bg-primary',
            'implemented' => 'bg-success',
            'partial' => 'bg-warning text-dark',
            'skipped' => 'bg-dark',
            'needs_review' => 'bg-danger',
        ][$this->status] ?? 'bg-secondary';
    }

    public static function statusOptions(): array
    {
        return [
            'not_started' => 'Not started',
            'planned' => 'Planned',
            'in_progress' => 'In progress',
            'implemented' => 'Implemented',
            'partial' => 'Partial',
            'skipped' => 'Skipped',
            'needs_review' => 'Needs review',
        ];
    }
}
