<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampImage extends Model
{
    public $timestamps = false;
    protected $fillable = ['camp_id', 'image', 'order'];

    public function camp(): BelongsTo
    {
        return $this->belongsTo(Camp::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
