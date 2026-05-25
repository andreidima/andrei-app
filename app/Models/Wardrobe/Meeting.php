<?php

namespace App\Models\Wardrobe;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Meeting extends Model
{
    use HasFactory;

    protected $table = 'wardrobe_meetings';
    protected $guarded = [];
    protected $casts = [
        'met_at' => 'datetime',
    ];

    public function path(): string
    {
        return route('wardrobe.meetings.show', $this);
    }

    public function outfitPhotoUrl(): ?string
    {
        return $this->outfit_photo_path ? Storage::disk('public')->url($this->outfit_photo_path) : null;
    }

    public function displayTitle(): string
    {
        if ($this->title) {
            return $this->title;
        }

        $contacts = $this->relationLoaded('people')
            ? $this->people->pluck('name')->join(', ')
            : $this->people()->pluck('name')->join(', ');

        return trim(($contacts ?: 'Meeting') . ' - ' . $this->met_at?->format('Y-m-d'));
    }

    public function people(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'wardrobe_meeting_person')
            ->withTimestamps();
    }

    public function clothingItems(): BelongsToMany
    {
        return $this->belongsToMany(ClothingItem::class, 'wardrobe_clothing_item_meeting')
            ->withTimestamps();
    }
}
