<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampActivity extends Model
{
    public $timestamps = false;
    protected $fillable = ['camp_id', 'title', 'description', 'order'];
    protected $casts = ['title' => 'array', 'description' => 'array'];

    public function camp(): BelongsTo
    {
        return $this->belongsTo(Camp::class);
    }
}
