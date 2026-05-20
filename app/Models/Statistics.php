<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Statistics extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'title',
        'count',
        'icon_name',
    ];
}
