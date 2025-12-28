<?php

namespace App\Http\Controllers\API;

use App\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\ProjectResource;

class ProjectController extends Controller
{
public function index()
    {
        $limit = request()->query('limit');

        // مفتاح الكاش حسب وجود limit
        $cacheKey = $limit
            ? "projects:limit:$limit"
            : "projects:all";

        // تشغيل الكاش
        $projects = Cache::remember($cacheKey, 3600, function () use ($limit) {
            $query = Project::active()
                ->ordered()
                ->with('images');

            // لو موجود limit نطبقه
            if ($limit) {
                $query->limit($limit);
            }

            return $query->get();
        });

        return ProjectResource::collection($projects);
    }
    // عرض مشروع واحد
    public function show($id)
    {
        try {
            $project = Cache::remember("project:{$id}", 3600, function () use ($id) {
                return Project::active()
                    ->with('images')
                    ->findOrFail($id);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        return new ProjectResource($project);
    }
}
