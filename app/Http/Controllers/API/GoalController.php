<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    public function index()
    {
        $goals = Goal::orderBy('order_index', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب أهداف المؤسسة بنجاح.',
            'data'    => $goals
        ], 200);
    }
}
