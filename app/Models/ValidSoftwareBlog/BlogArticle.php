<?php

namespace App\Models\ValidSoftwareBlog;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogArticle extends Model
{
    use HasFactory;

    protected $table = 'validsoftware_blog_articles';

    protected $guarded = [];

    protected $casts = [
        'sent_to_vali_at' => 'date',
        'published_at' => 'date',
    ];

    public function path(): string
    {
        return "/articole-validsoftware/articole/{$this->id}";
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(BlogProject::class, 'blog_project_id');
    }

    public static function typeOptions(): array
    {
        return [
            'project_case_study' => 'Studiu de caz proiect',
            'technical_problem_solution' => 'Articol tehnic / problem-solution',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'not_started' => 'Neinceput',
            'needs_project_review' => 'Necesita inspectarea proiectului',
            'drafted' => 'Draft scris',
            'sent_to_vali' => 'Trimis catre Vali',
            'published' => 'Publicat',
            'needs_revision' => 'Necesita revizie',
        ];
    }

    public function typeLabel(): string
    {
        return self::typeOptions()[$this->type] ?? $this->type;
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? $this->status;
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'published' => 'bg-success',
            'sent_to_vali' => 'bg-primary',
            'drafted' => 'bg-info text-dark',
            'needs_revision' => 'bg-danger',
            'needs_project_review' => 'bg-warning text-dark',
            default => 'bg-secondary',
        };
    }

    public function hasSimilarTypeWarning(): bool
    {
        if (! $this->blog_project_id || ! $this->type) {
            return false;
        }

        return self::query()
            ->where('blog_project_id', $this->blog_project_id)
            ->where('type', $this->type)
            ->when($this->exists, fn ($query) => $query->whereKeyNot($this->id))
            ->exists();
    }
}
