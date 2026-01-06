<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'person_name',
        'program_name',
        'rating',
        'comment',
        'is_published'
    ];
}

