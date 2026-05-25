<?php

namespace App\Models\Wardrobe;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class ClothingItem extends Model
{
    use HasFactory;

    protected $table = 'wardrobe_clothing_items';
    protected $guarded = [];

    public function path(): string
    {
        return route('wardrobe.clothing-items.show', $this);
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }

    public function meetings(): BelongsToMany
    {
        return $this->belongsToMany(Meeting::class, 'wardrobe_clothing_item_meeting')
            ->withTimestamps();
    }
}
