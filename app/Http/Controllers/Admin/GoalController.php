<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GoalController extends Controller
{
    public function index()
    {
        $goals = Goal::orderBy('order_index', 'asc')->get();

        return response()->json([
            'success' => true,
            'data'    => $goals
        ], 200);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'text'        => 'required|string',
            'order_index' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $maxOrder = Goal::max('order_index');
        $nextOrder = $maxOrder !== null ? $maxOrder + 1 : 1;

        $goal = Goal::create([
            'text'        => $request->text,
            'order_index' => $request->order_index ?? $nextOrder,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة الهدف بنجاح.',
            'data'    => $goal
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $goal = Goal::find($id);

        if (!$goal) {
            return response()->json(['success' => false, 'message' => 'الهدف غير موجود.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'text'        => 'required|string',
            'order_index' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $goal->update([
            'text'        => $request->text,
            'order_index' => $request->order_index ?? $goal->order_index,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الهدف بنجاح.',
            'data'    => $goal
        ], 200);
    }

  
    public function destroy($id)
    {
        $goal = Goal::find($id);

        if (!$goal) {
            return response()->json(['success' => false, 'message' => 'الهدف غير موجود.'], 404);
        }

        $goal->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الهدف بنجاح.'
        ], 200);
    }

}
