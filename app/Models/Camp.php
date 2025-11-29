<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Camp extends Model
{
    protected $fillable = [
        'title', 'description', 'about', 'main_image', 'age_range',
        'start_date', 'duration', 'capacity', 'is_open', 'status', 'order'
    ];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'about' => 'array',
        'start_date' => 'date',
        'is_open' => 'boolean',
    ];

    public function locations(): HasMany
    {
        return $this->hasMany(CampLocation::class)->orderBy('order');
    }

    public function learnings(): HasMany
    {
        return $this->hasMany(CampLearning::class)->orderBy('order');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CampActivity::class)->orderBy('order');
    }

    public function images(): HasMany
    {
        return $this->hasMany(CampImage::class)->orderBy('order');
    }

    public function scopeOpen($query)
    {
        return $query->where('is_open', true);
    }

    public function scopeClosed($query)
    {
        return $query->where('is_open', false);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('start_date', 'desc')->orderBy('order');
    }

    public function getMainImageUrlAttribute()
    {
        return $this->main_image ? asset('storage/' . $this->main_image) : null;
    }
}
