<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiReply extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'review_id',
        'reply_text',
        'input_tokens',
        'output_tokens',
        'total_tokens',
        'model_used'
    ];

    protected $casts = [
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'total_tokens' => 'integer',
    ];   

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}