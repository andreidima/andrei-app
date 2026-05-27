<?php

namespace App\Models\ValidSoftwareBlog;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogProject extends Model
{
    use HasFactory;

    protected $table = 'validsoftware_blog_projects';

    protected $guarded = [];

    public function path(): string
    {
        return "/articole-validsoftware/proiecte/{$this->id}";
    }

    public function articles(): HasMany
    {
        return $this->hasMany(BlogArticle::class, 'blog_project_id');
    }

    public static function statusOptions(): array
    {
        return [
            'active' => 'Activ',
            'archived' => 'Arhivat',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? $this->status;
    }
}
