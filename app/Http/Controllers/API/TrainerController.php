<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TrainerResource;
use App\Models\Trainer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TrainerController extends Controller
{
    /**
     * Display a listing of trainers.
     *
     * GET /api/v1/trainers
     *
     * Response:
     * [
     *   {
     *     "id": 1,
     *     "name": "أحمد محمد",
     *     "image": "https://api.hemam.com/storage/trainers/trainer1.jpg",
     *     "bio": "مدرب معتمد في القيادة..."
     *   }
     * ]
     */
    public function index()
    {
        try {
            // Cache للبيانات لمدة ساعة
            $trainers = Cache::remember('trainers:all', 3600, function () {
                return Trainer::active()
                    ->ordered()
                    ->get();
            });

            return TrainerResource::collection($trainers);

        } catch (\Throwable $e) {
            Log::error('Trainers API Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء جلب المدربين',
            ], 500);
        }
    }
}
