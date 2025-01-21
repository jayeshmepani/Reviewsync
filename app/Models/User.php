<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'email_verified',
        'first_name',
        'google_avatar_original',
        'google_expires_in',
        'google_id',
        'google_refresh_token',
        'google_scopes',
        'google_token',
        'last_name',
        'name',
        'password',
        'phone',
        'profile_picture',
        'uuid',
        'role'
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function isSuperAdmin()
    {
        return $this->role === 'superadmin';
    }

    // Scope to exclude superadmin from regular queries
    public function scopeRegularUsers($query)
    {
        return $query->where('role', 'user');
    }
}
