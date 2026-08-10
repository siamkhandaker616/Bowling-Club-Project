<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Personality extends Model
{
    protected $fillable = ['name', 'description'];

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(Staff::class, 'staff_personalities');
    }
}
