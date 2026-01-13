<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Http\Resources\ProjectResource;
use App\Models\Activity;
use App\Models\Project;
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

            //  helper SQL snippet
            $sqlTitle = "JSON_UNQUOTE(JSON_EXTRACT(title, '$.\"%s\"')) LIKE ?";
            $sqlDesc  = "JSON_UNQUOTE(JSON_EXTRACT(description, '$.\"%s\"')) LIKE ?";

            // =====================================================
            // 📌 1) Activity
            // =====================================================
            $activities = Activity::where('is_open', true)
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
            // 🔢 Total Count
            // =====================================================
            $totalResults =
                $activities->count() +
                $projects->count() ;
            return response()->json([
                'query' => $searchTerm,
                'total_results' => $totalResults,
                'activities' => ActivityResource::collection($activities),
                'projects' => ProjectResource::collection($projects),
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
