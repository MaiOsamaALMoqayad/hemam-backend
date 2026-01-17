<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityRequest extends Model
{
    protected $fillable = [
        'name',
        'age',
        'phone',
        'gender',
        'activity_id',
        'is_read',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
    
}

