<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityHistoryImage extends Model
{
    protected $fillable = [
        'history_id', // العمود المستخدم في قاعدة البيانات
        'image',
    ];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function history()
    {
        // تأكد أن المفتاح الأجنبي هو history_id
        return $this->belongsTo(ActivityHistory::class, 'activity_history_id');
    }
    }

