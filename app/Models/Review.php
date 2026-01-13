<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'person_name',
        'activity_id',
        'rating',
        'comment',
        'is_published',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}


