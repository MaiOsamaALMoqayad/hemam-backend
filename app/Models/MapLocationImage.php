<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MapLocationImage extends Model
{
    protected $fillable = [
    'map_location_id',
    'image'
];
public function getImageAttribute($value)
    {
        if ($value) {
            // سيقوم بإرجاع الرابط كاملاً مثل http://127.0.0.1:8000/storage/map_locations/name.png
            return asset(Storage::url($value));
        }
        return null;
    }

    public function mapLocation()
    {
        return $this->belongsTo(MapLocation::class);
    }
}
