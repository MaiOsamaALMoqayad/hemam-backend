<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\StatisticsResource;
use App\Models\Statistics;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StatisticsController extends Controller
{
    /**
     * Display statistics.
     *
     * GET /api/v1/statistics
     *
     * Response:
     * {
     *   "beneficiaries_count": 1500,
     *   "institutions_count": 25,
     *   "trainings_count": 150,
     *   "consultations_count": 300
     * }
     */
    public function index()
    {
        try {
            // Cache للبيانات لمدة ساعة
            $statistics = Cache::remember('statistics:latest', 3600, function () {
                // جلب أول سجل (لأنه عندنا سجل واحد فقط)
                return Statistics::first();
            });

            // إذا ما في إحصائيات، نرجع أصفار
            if (!$statistics) {
                return response()->json([
                    'beneficiaries_count' => 0,
                    'institutions_count' => 0,
                    'trainings_count' => 0,
                    'consultations_count' => 0,
                ]);
            }

            return new StatisticsResource($statistics);

        } catch (\Throwable $e) {
            Log::error('Statistics API Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء جلب الإحصائيات',
            ], 500);
        }
    }
}
