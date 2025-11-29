<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampLearning extends Model
{
    public $timestamps = false;
    protected $fillable = ['camp_id', 'title', 'order'];
    protected $casts = ['title' => 'array'];

    public function camp(): BelongsTo
    {
        return $this->belongsTo(Camp::class);
    }
}
