<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CampResource;
use App\Http\Resources\CampDetailResource;
use App\Models\Camp;
use Illuminate\Support\Facades\Cache;

class CampController extends Controller
{

    public function index()
    {
        $camps = Cache::remember('camps:all_separated', 3600, function () {
            $openCamps = Camp::open()->ordered()->with('locations')->get();
            $closedCamps = Camp::closed()->ordered()->with('locations')->get();

            return [
                'open' => $openCamps,
                'closed' => $closedCamps,
            ];
        });

        return response()->json([
            'open' => CampResource::collection($camps['open']),
            'closed' => CampResource::collection($camps['closed']),
        ]);
    }

    /**
     * عرض المخيمات المفتوحة
     */
    public function open()
    {
        $camps = Cache::remember('camps:open', 3600, function () {
            return Camp::open()
                ->ordered()
                ->with('locations')
                ->get();
        });

        return CampResource::collection($camps);
    }

    /**
     * عرض المخيمات المغلقة
     */
    public function closed()
    {
        $camps = Cache::remember('camps:closed', 3600, function () {
            return Camp::closed()
                ->ordered()
                ->with('locations')
                ->get();
        });

        return CampResource::collection($camps);
    }

    /**
     * عرض تفاصيل مخيم معين
     */
    public function show(Camp $camp)
    {
        $campDetails = Cache::remember("camp:details:{$camp->id}", 3600, function () use ($camp) {
            return $camp->load(['locations', 'learnings', 'activities', 'images']);
        });

        return new CampDetailResource($campDetails);
    }
    
}
