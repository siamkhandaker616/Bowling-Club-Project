<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewVote extends Model
{
    protected $fillable = [
        'review_id', 'review_type', 'voter_id', 'vote',
    ];
}
