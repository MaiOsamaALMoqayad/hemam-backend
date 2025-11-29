<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampLocation extends Model
{
    public $timestamps = false;
    protected $fillable = ['camp_id', 'name', 'order'];
    protected $casts = ['name' => 'array'];

    public function camp(): BelongsTo
    {
        return $this->belongsTo(Camp::class);
    }
}
