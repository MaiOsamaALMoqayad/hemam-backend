<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    protected $fillable = [
        'full_name',
        'phone',
        'country',
        'email',
        'amount',
        'currency',
        'message'
    ];
}

