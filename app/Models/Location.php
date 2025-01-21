<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'store_code',
        'name',
        'title',
        'website_uri',
        'primary_phone',
        'primary_category',
        'address_lines',
        'locality',
        'region',
        'postal_code',
        'country_code',
        'latitude',
        'longitude',
        'status',
        'description',
        'place_id',
        'maps_uri',
        'new_review_uri',
        'formatted_address',
        'user_id',
        'id'
    ];

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function localReviews()
    {
        return $this->hasMany(LocalReview::class);
    }

       /**
     * Get the user that owns the location.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
