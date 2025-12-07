<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainerApplication;
use Illuminate\Http\Request;

class TrainerApplicationController extends Controller
{
    public function index(Request $request)
    {
        $query = TrainerApplication::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->latest()->get());
    }

    public function show(TrainerApplication $trainerApplication)
    {
        return response()->json($trainerApplication);
    }

    public function updateStatus(Request $request, TrainerApplication $trainerApplication)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'admin_notes' => 'nullable|string',
        ]);

        $trainerApplication->update($data);
        return response()->json(['message' => 'تم التحديث', 'application' => $trainerApplication]);
    }

    public function destroy(TrainerApplication $trainerApplication)
    {
        $trainerApplication->delete();
        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
