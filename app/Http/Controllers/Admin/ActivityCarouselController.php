<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\ActivityImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ActivityCarouselController extends Controller
{
    // إضافة صور جديدة
public function store(Request $request, $activityId)
{
    $request->validate([
        'images.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048'
    ]);

    $activity = Activity::findOrFail($activityId);
    $uploadedImages = [];

    foreach ($request->file('images') as $img) {
        $path = $img->store('activities', 'public');

        // قم بتخزين الصورة
        $newImage = $activity->images()->create(['image' => $path]);

        // أضف الرابط الكامل للمصفوفة التي ستعود في الـ Response
        $uploadedImages[] = [
            'id' => $newImage->id,
            'image_url' => asset('storage/' . $newImage->image), // الرابط الكامل
        ];
    }

    Cache::forget("activities:{$activityId}");

    return response()->json([
        'message' => 'تم رفع الصور بنجاح',
        'data' => $uploadedImages
    ], 201);
}

    // حذف صورة موجودة
    public function destroy($id)
    {
        $image = ActivityImage::findOrFail($id);

        Storage::disk('public')->delete($image->image);
        $activityId = $image->activity_id;
        $image->delete();

        Cache::forget("activities:{$activityId}"); // مسح الكاش

        return response()->json([
            'message' => 'تم حذف الصورة بنجاح'
        ]);
    }

    // جلب جميع الصور لنشاط معين
    public function index($activityId)
    {
        $activity = Activity::findOrFail($activityId);

        $images = $activity->images->map(function ($img) {
            return [
                'id' => $img->id,
                'url' => asset('storage/' . $img->image)
            ];
        });

        return response()->json([
            'data' => $images
        ]);
    }
}
