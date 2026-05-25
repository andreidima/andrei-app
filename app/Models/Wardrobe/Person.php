<?php

namespace App\Models\Wardrobe;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Person extends Model
{
    use HasFactory;

    protected $table = 'wardrobe_people';
    protected $guarded = [];

    public const CONTACT_TYPES = [
        'person' => 'Person',
        'couple' => 'Couple',
        'family' => 'Family',
        'group' => 'Group',
    ];

    public function contactTypeLabel(): string
    {
        return self::CONTACT_TYPES[$this->contact_type] ?? self::CONTACT_TYPES['person'];
    }

    public function path(): string
    {
        return route('wardrobe.people.show', $this);
    }

    public function meetings(): BelongsToMany
    {
        return $this->belongsToMany(Meeting::class, 'wardrobe_meeting_person')
            ->withTimestamps();
    }
}
