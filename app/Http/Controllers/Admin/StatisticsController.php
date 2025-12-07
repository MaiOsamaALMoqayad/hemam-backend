<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Statistics;

class StatisticsController extends Controller
{
    public function index()
    {
        return response()->json(Statistics::first());
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'beneficiaries_count' => 'required|integer|min:0',
            'institutions_count' => 'required|integer|min:0',
            'trainings_count' => 'required|integer|min:0',
            'consultations_count' => 'required|integer|min:0',
        ]);

        $stats = Statistics::first();
        $stats->update($data);
        Cache::forget('statistics:latest');

        return response()->json($stats);
    }
}
