<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
class Review extends Model
{
    use HasFactory;

    protected $table = 'reviews';

    protected $fillable = [
        'review_id',
        'user_id',
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

    public function aiReplies()
    {
        return $this->hasMany(AiReply::class);
    }

    /**
     * Get the user that owns the review.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    /**
     * Scope a query to only include reviews owned by a specific user.
     */
    public function scopeOwnedBy(Builder $query, $userId)
    {
        return $query->where('user_id', $userId);
    }

}
