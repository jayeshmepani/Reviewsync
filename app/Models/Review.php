<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $table = 'reviews';

    protected $fillable = [
        'review_id',
        'reviewer_name',
        'profile_photo_url',
        'star_rating',
        'comment',
        'create_time',
        'update_time',
        'reply_comment',
        'reply_update_time',
        'review_name',
        'location_id'
    ];

    protected $dates = [
        'create_time',
        'update_time',
        'reply_update_time'
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

}
