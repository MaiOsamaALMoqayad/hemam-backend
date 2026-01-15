<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KhaterPost extends Model
{
    use HasFactory;

    protected $table = 'khater_posts';

    protected $fillable = [
        'khater_category_id',
        'title',
        'excerpt',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * العلاقة: المقال ينتمي لتصنيف
     */
    public function category()
    {
        return $this->belongsTo(KhaterCategory::class, 'khater_category_id');
    }

    /**
     * العلاقة: المقال يحتوي صور كاروسيل
     */
    public function images()
    {
        return $this->hasMany(KhaterPostImage::class, 'khater_post_id');
    }
}
