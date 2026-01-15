<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KhaterCategory extends Model
{
    use HasFactory;

    protected $table = 'khater_categories';

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * العلاقة: التصنيف يحتوي عدة مقالات
     */
    public function posts()
    {
        return $this->hasMany(KhaterPost::class, 'khater_category_id');
    }
}
