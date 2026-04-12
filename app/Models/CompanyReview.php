<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyReview extends Model
{
    protected $fillable = [
        'person_name',
        'rating',
        'comment',
        'is_published',
    ];
}
