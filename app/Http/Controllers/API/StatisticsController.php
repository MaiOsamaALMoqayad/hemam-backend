<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\StatisticsResource;
use App\Models\Statistics;


class StatisticsController extends Controller
{

   public function index()
    {
        $statistics = Statistics::latest()->take(4)->get();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب الإحصائيات الأربعة بنجاح.',
            'data'    => $statistics
        ], 200);
    }
}
