<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnnualProgramResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\CampResource;
use App\Models\AnnualProgram;
use App\Models\Project;
use App\Models\Camp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = $request->input('q');

            if (!$query || strlen(trim($query)) < 2) {
                return response()->json([
                    'status' => false,
                    'message' => 'يرجى إدخال كلمة بحث (حرفين على الأقل)',
                ], 400);
            }

            $searchTerm = trim($query);
            $locale = app()->getLocale();

            // 🔍 helper SQL snippet
            $sqlTitle = "JSON_UNQUOTE(JSON_EXTRACT(title, '$.\"%s\"')) LIKE ?";
            $sqlDesc  = "JSON_UNQUOTE(JSON_EXTRACT(description, '$.\"%s\"')) LIKE ?";

            // =====================================================
            // 📌 1) Annual Programs
            // =====================================================
            $annualPrograms = AnnualProgram::where('is_open', true)
                ->where(function ($q) use ($searchTerm, $locale, $sqlTitle, $sqlDesc) {
                    $q->whereRaw(sprintf($sqlTitle, $locale), ["%{$searchTerm}%"])
                      ->orWhereRaw(sprintf($sqlDesc, $locale), ["%{$searchTerm}%"])
                      ->orWhereRaw(sprintf($sqlTitle, 'ar'), ["%{$searchTerm}%"])
                      ->orWhereRaw(sprintf($sqlDesc, 'ar'), ["%{$searchTerm}%"]);
                })
                ->ordered()
                ->limit(10)
                ->get();

            // =====================================================
            // 📌 2) Projects
            // =====================================================
            $projects = Project::active()
                ->where(function ($q) use ($searchTerm, $locale, $sqlTitle, $sqlDesc) {
                    $q->whereRaw(sprintf($sqlTitle, $locale), ["%{$searchTerm}%"])
                      ->orWhereRaw(sprintf($sqlDesc, $locale), ["%{$searchTerm}%"])
                      ->orWhereRaw(sprintf($sqlTitle, 'ar'), ["%{$searchTerm}%"])
                      ->orWhereRaw(sprintf($sqlDesc, 'ar'), ["%{$searchTerm}%"]);
                })
                ->with('images')
                ->ordered()
                ->limit(10)
                ->get();

            // =====================================================
            // 📌 3) Camps
            // =====================================================
            $camps = Camp::where('is_open', true)
                ->where(function ($q) use ($searchTerm, $locale, $sqlTitle, $sqlDesc) {
                    $q->whereRaw(sprintf($sqlTitle, $locale), ["%{$searchTerm}%"])
                      ->orWhereRaw(sprintf($sqlDesc, $locale), ["%{$searchTerm}%"])
                      ->orWhereRaw(sprintf($sqlTitle, 'ar'), ["%{$searchTerm}%"])
                      ->orWhereRaw(sprintf($sqlDesc, 'ar'), ["%{$searchTerm}%"]);
                })
                ->with('locations')
                ->ordered()
                ->limit(10)
                ->get();

            // =====================================================
            // 🔢 Total Count
            // =====================================================
            $totalResults =
                $annualPrograms->count() +
                $projects->count() +
                $camps->count();

            return response()->json([
                'query' => $searchTerm,
                'total_results' => $totalResults,
                'annual_programs' => AnnualProgramResource::collection($annualPrograms),
                'projects' => ProjectResource::collection($projects),
                'camps' => CampResource::collection($camps),
            ]);

        } catch (\Throwable $e) {
            Log::error('Search API Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء البحث',
            ], 500);
        }
    }
}
