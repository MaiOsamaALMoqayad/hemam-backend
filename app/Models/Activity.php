<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image',
        'season',               // جديد: الصيفي أو الشتوي
        'order',
        'is_active',            // بدل is_open
        'application_deadline',
        'duration',
        'capacity',
    ];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Scope للأنشطة النشطة فقط
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
        $data = $this->{$field}; // بسبب $casts
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

    /**
     * العلاقة مع histories
     */
// داخل كلاس Activity
public function activity_histories()
{
    return $this->histories();
}
public function histories()
{
    return $this->hasMany(ActivityHistory::class);
}
public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    public function images()
{
    return $this->hasMany(ActivityImage::class);
}
    public function requests()
    {
        return $this->hasMany(ActivityRequest::class);
    }

}
