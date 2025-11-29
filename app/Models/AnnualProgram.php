<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnualProgram extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image',
        'order',
        'is_active',
    ];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Scope للبرامج النشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope للترتيب
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('created_at', 'desc');
    }

    /**
     * Get translated title
     */
    public function getTitleAttribute($value)
    {
        $data = json_decode($value, true);
        $locale = app()->getLocale();
        return $data[$locale] ?? $data['ar'] ?? '';
    }

    /**
     * Get translated description
     */
    public function getDescriptionAttribute($value)
    {
        $data = json_decode($value, true);
        $locale = app()->getLocale();
        return $data[$locale] ?? $data['ar'] ?? '';
    }

    /**
     * Get full image URL
     */
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
