<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KhaterPostImage extends Model
{
    use HasFactory;

    protected $table = 'khater_post_images';

    protected $fillable = [
        'khater_post_id',
        'image_path'
    ];

    /**
     * العلاقة: الصورة تتبع لمقال
     */
    public function post()
    {
        return $this->belongsTo(KhaterPost::class, 'khater_post_id');
    }
}
