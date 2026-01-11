<?php

namespace App\Http\Controllers\API;

use App\Models\Activity;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\ActivityResource;
use App\Http\Resources\ActivitySummaryResource;

class ActivityController extends Controller
{
    /**
     * GET /api/v1/activities
     * عرض جميع الأنشطة
     */
    public function index()
    {
        try {
            $activities = Cache::remember('activities:all', 3600, function () {
                return Activity::orderBy('order', 'asc')->get();
            });

            return ActivitySummaryResource::collection($activities);

        } catch (\Throwable $e) {
            Log::error('Activities API Error (index): ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء جلب الأنشطة',
            ], 500);
        }
    }

    /**
     * GET /api/v1/activities/{id}
     * عرض نشاط واحد مع التاريخ والهستوري
     */
    public function show($id)
    {
        try {
            $activity = Cache::remember("activities:{$id}", 3600, function () use ($id) {
                return Activity::with(['histories.images'])->findOrFail($id);
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
}
