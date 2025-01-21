<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocalReview extends Model
{
    protected $fillable = [
        'review_id',
        'reviewer_name',
        'star_rating',
        'comment',
        'create_time',
        'location_id'
    ];

    protected $casts = [
        'create_time' => 'datetime',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}