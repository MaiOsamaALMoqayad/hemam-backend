<?php

namespace App\Http\Controllers\API;

use App\Models\AnnualProgram;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\AnnualProgramResource;
  use App\Http\Resources\AnnualProgramSummaryResource;
class AnnualProgramController extends Controller
{
    /**
     * GET /api/v1/annual-programs
     * عرض جميع البرامج السنوية
     */


public function index()
{
    try {
        $programs = Cache::remember('annual_programs:all', 3600, function () {
            return AnnualProgram::orderBy('order', 'asc')->get();
        });

        return AnnualProgramSummaryResource::collection($programs);

    } catch (\Throwable $e) {
        Log::error('Annual Programs API Error (index): ' . $e->getMessage());

        return response()->json([
            'status' => false,
            'message' => 'حدث خطأ أثناء جلب البرامج',
        ], 500);
    }
}



    /**
     * GET /api/v1/annual-programs/{id}
     * عرض برنامج سنوي واحد بالتاريخ والهستوري
     */
public function show($id)
{
    try {
        $program = Cache::remember("annual_programs:{$id}", 3600, function () use ($id) {
            return AnnualProgram::with('histories')->findOrFail($id);
        });

        return new AnnualProgramResource($program);

    } catch (\Throwable $e) {
        Log::error("Annual Program API Error (show): " . $e->getMessage());

        return response()->json([
            'status' => false,
            'message' => 'البرنامج المطلوب غير موجود',
        ], 404);
    }
}


}
