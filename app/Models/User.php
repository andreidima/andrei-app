<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || ($this->role === null && $this->isPrimaryOwner());
    }

    public function canAccessApartments(): bool
    {
        return in_array($this->role, ['admin', 'apartments'], true) || ($this->role === null && $this->isPrimaryOwner());
    }

    public function capabilities(): array
    {
        return [
            'pontaje' => $this->isAdmin(),
            'apartamente' => $this->canAccessApartments(),
        ];
    }

    public static function roleOptions(): array
    {
        return [
            'admin' => 'Admin',
            'apartments' => 'Apartments',
        ];
    }

    private function isPrimaryOwner(): bool
    {
        return (int) $this->id === 1
            || in_array($this->email, ['adima@validsoftware.ro', 'andrei.dima@usm.ro'], true);
    }
}
