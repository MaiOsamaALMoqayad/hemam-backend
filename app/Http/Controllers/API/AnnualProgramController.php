<?php

namespace App\Http\Controllers\API;

use App\Models\AnnualProgram;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\AnnualProgramResource;

class AnnualProgramController extends Controller
{
    /**
     * Display a listing of annual programs.
     *
     * GET /api/v1/annual-programs
     *
     * Response:
     * [
     *   {
     *     "id": 1,
     *     "title": "رواحل المجد",
     *     "description": "رحلة روحانية...",
     *     "image": "https://api.hemam.com/storage/annual_programs/almagd.jpg"
     *   }
     * ]
     */
    public function index()
    {   try {
        // Cache للبيانات لمدة ساعة (3600 ثانية)
        $programs = Cache::remember('annual_programs:all', 3600, function () {
            // جلب البرامج النشطة فقط، مرتبة
            return AnnualProgram::active()
                ->ordered()
                ->get();
        });

        // تحويل البيانات باستخدام Resource
        return AnnualProgramResource::collection($programs);
  } catch (\Throwable $e) {
    // Log the error for debugging
    Log::error('Annual Programs API Error: ' . $e->getMessage());

    return response()->json([
        'status' => false,
        'message' => 'حدث خطأ أثناء جلب البرامج',
    ], 500);
}
    }
}
