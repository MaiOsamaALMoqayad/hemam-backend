<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityHistory extends Model
{
    protected $fillable = [
        'activity_id',
        'year',
        'achievements',
    ];

    protected $casts = [
        'achievements' => 'array',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

  public function images()
{
    // تأكد أن الاسم هنا يطابق ما هو موجود في قاعدة البيانات
    return $this->hasMany(ActivityHistoryImage::class, 'activity_history_id');
}
}
