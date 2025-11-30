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
    $projects = Cache::remember('projects:all', 3600, function () {
        return Project::active()
            ->ordered()
            ->with('images')
            ->get();
    });

    return ProjectResource::collection($projects);
}
}
