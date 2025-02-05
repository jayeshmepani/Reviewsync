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
        'last_name',
        'google_id',
        'google_token',
        'name',
        'password',
        'profile_picture',
        'uuid',
        'role',
        'subscription',
        'subscription_billing_start',
        'subscription_billing_end'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
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
            'subscription_billing_start' => 'datetime',
            'subscription_billing_end' => 'datetime',
        ];
    }

    public function isTrial()
    {
        return $this->subscription === 'trial';
    }

    public function isStandard()
    {
        return $this->subscription === 'standard';
    }

    public function isPremium()
    {
        return $this->subscription === 'premium';
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
        return $this->role === 'superadmin' && is_null($this->subscription);
    }

    public function isUser()
    {
        return $this->role === 'user';
    }


    // Scope to exclude superadmin from regular queries
    public function scopeRegularUsers($query)
    {
        return $query->where('role', 'user');
    }
}
