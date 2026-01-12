<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ActivityResource;
use App\Http\Resources\ActivitySummaryResource;
use Intervention\Image\Laravel\Facades\Image;

class ActivityController extends Controller
{
    public function index()
    {
        $activities = Cache::remember('activities:all', 3600, function() {
            return Activity::orderBy('order', 'asc')->get();
        });

        return ActivitySummaryResource::collection($activities);
    }

    public function show($id)
    {
        try {
            $activity = Cache::remember("activities:{$id}", 3600, function () use ($id) {
                return Activity::findOrFail($id);
            });

            return new ActivityResource($activity);
        } catch (\Throwable $e) {
            Log::error("Activity API Error (show): " . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'النشاط المطلوب غير موجود',
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title_ar' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'description_ar' => 'required|string',
            'description_en' => 'nullable|string',
            'image' => 'required|image|max:2048',
            'order' => 'nullable|integer',
            'is_open' => 'nullable|boolean',
            'application_deadline' => 'nullable|string',
            'duration' => 'nullable|string',
            'capacity' => 'nullable|string',
            'season' => 'nullable|string',
            'history' => 'nullable|array',
        ]);
        $data['history'] = $data['history'] ?? [];

        // حفظ الصورة الرئيسية
        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = Image::read($request->file('image'))->cover(800, 600);
            $filename = uniqid() . '.jpg';
            $image->save(storage_path('app/public/activities/' . $filename), 85);
            $imagePath = 'activities/' . $filename;
        }

        $activity = Activity::create([
            'title' => [
                'ar' => $data['title_ar'],
                'en' => $data['title_en'] ?? '',
            ],
            'description' => [
                'ar' => $data['description_ar'],
                'en' => $data['description_en'] ?? '',
            ],
            'image' => $imagePath,
            'order' => $data['order'] ?? 0,
            'is_open' => $data['is_open'] ?? true,
            'season' => $data['season'] ?? '',
            'application_deadline' => $data['application_deadline'] ?? '',
            'duration' => $data['duration'] ?? '',
            'capacity' => $data['capacity'] ?? '',
        ]);

        if ($request->has('history')) {
            $this->processHistory($activity, $request->input('history'), $request);
        }

        Cache::forget('activities:all');

        return response()->json(['data' => new ActivityResource($activity)]);
    }

    public function update(Request $request, Activity $activity)
    {
        try {
            $data = $request->validate([
                'title_ar' => 'sometimes|string|max:255',
                'title_en' => 'sometimes|nullable|string|max:255',
                'description_ar' => 'sometimes|string',
                'description_en' => 'sometimes|nullable|string',
                'image' => 'sometimes|nullable|image|max:2048',
                'order' => 'sometimes|integer',
                'is_open' => 'sometimes',
                'season' => 'sometimes|string',
                'application_deadline' => 'sometimes|string',
                'duration' => 'sometimes|string',
                'capacity' => 'sometimes|string',
                'history' => 'sometimes|array',
            ]);

            // تحديث العنوان
            $title = is_string($activity->title) ? json_decode($activity->title, true) : $activity->title;
            if ($request->has('title_ar')) $title['ar'] = $data['title_ar'];
            if ($request->has('title_en')) $title['en'] = $data['title_en'] ?? '';

            // تحديث الوصف
            $description = is_string($activity->description) ? json_decode($activity->description, true) : $activity->description;
            if ($request->has('description_ar')) $description['ar'] = $data['description_ar'];
            if ($request->has('description_en')) $description['en'] = $data['description_en'] ?? '';

            // تحديث الصورة
            $imagePath = $activity->image;
            if ($request->hasFile('image')) {
                if ($imagePath) Storage::disk('public')->delete($imagePath);
                $image = Image::read($request->file('image'))->cover(800, 600);
                $filename = uniqid() . '.jpg';
                $image->save(storage_path('app/public/activities/' . $filename), 85);
                $imagePath = 'activities/' . $filename;
            }

            $activity->update([
                'title' => $title,
                'description' => $description,
                'image' => $imagePath,
                'order' => $data['order'] ?? $activity->order,
                'is_open' => $request->has('is_open') ? filter_var($data['is_open'], FILTER_VALIDATE_BOOLEAN) : $activity->is_open,
                'season' => $data['season'] ?? $activity->season,
                'application_deadline' => $data['application_deadline'] ?? $activity->application_deadline,
                'duration' => $data['duration'] ?? $activity->duration,
                'capacity' => $data['capacity'] ?? $activity->capacity,
            ]);

            if ($request->has('history')) {
                $this->processHistory($activity, $request->input('history'), $request);
            }

            Cache::forget('activities:all');
            Cache::forget("activities:{$activity->id}");

            return new ActivityResource($activity);
        } catch (\Throwable $e) {
            Log::error("Activity Update Error: " . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء التحديث',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Activity $activity)
    {
        if ($activity->image) {
            Storage::disk('public')->delete($activity->image);
        }

        foreach ($activity->histories as $history) {
            foreach ($history->images as $img) {
                Storage::disk('public')->delete($img->image);
            }
        }

        $activity->delete();
        Cache::forget('activities:all');

        return response()->json(['message' => 'تم حذف النشاط بنجاح']);
    }

    private function processHistory($activity, $historyData, $request)
    {
        DB::transaction(function () use ($activity, $historyData, $request) {
            $keepHistoryIds = [];

            foreach ($historyData as $index => $h) {

                if (!isset($h['year'])) continue;

                $history = null;
                if (isset($h['id'])) {
                    $history = $activity->histories()->find($h['id']);
                }

                if (!$history) {
                    $history = $activity->histories()->create([
                        'year' => $h['year'],
                        'achievements' => json_encode($h['achievements'] ?? [])
                    ]);
                } else {
                    $history->update([
                        'year' => $h['year'],
                        'achievements' => json_encode($h['achievements'] ?? [])
                    ]);
                }

                $keepHistoryIds[] = $history->id;

                // حذف الصور غير الموجودة
                $existingImageIds = $h['existing_images'] ?? [];
                if (!is_array($existingImageIds)) $existingImageIds = [];

                foreach ($history->images as $img) {
                    if (!in_array($img->id, $existingImageIds)) {
                        Storage::disk('public')->delete($img->image);
                        $img->delete();
                    }
                }

                // إضافة صور جديدة
                if ($request->hasFile("history.$index.images")) {
                    foreach ($request->file("history.$index.images") as $file) {
                        $image = Image::read($file)->cover(400, 300);
                        $filename = uniqid() . '_h.jpg';
                        $image->save(storage_path("app/public/activities/$filename"), 85);

                        $history->images()->create([
                            'image' => "activities/$filename"
                        ]);
                    }
                }
            }

            // حذف السنين القديمة
            foreach ($activity->histories as $oldHistory) {
                if (!in_array($oldHistory->id, $keepHistoryIds)) {
                    foreach ($oldHistory->images as $img) {
                        Storage::disk('public')->delete($img->image);
                    }
                    $oldHistory->delete();
                }
            }
        });
    }
}
