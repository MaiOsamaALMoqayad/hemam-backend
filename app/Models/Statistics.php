<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Statistics extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'title',
        'count',
        'icon_name',
        'order_index',
    ];

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index')->orderBy('id');
    }
}
