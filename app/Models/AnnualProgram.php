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
     * ترجمة الحقل حسب اللغة الحالية
     */
    public function getTranslated(string $field): string
    {
        $data = $this->{$field}; // بسبب $casts، القيمة مصفوفة جاهزة
        $locale = app()->getLocale();

        return $data[$locale] ?? $data['ar'] ?? '';
    }

    /**
     * رابط الصورة الكامل
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
