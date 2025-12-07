<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpertConsultation;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    public function index(Request $request)
    {
        $query = ExpertConsultation::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->latest()->get());
    }

    public function show(ExpertConsultation $consultation)
    {
        return response()->json($consultation);
    }

    public function updateStatus(Request $request, ExpertConsultation $consultation)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
            'admin_notes' => 'nullable|string',
        ]);

        $consultation->update($data);
        return response()->json(['message' => 'تم التحديث', 'consultation' => $consultation]);
    }

    public function destroy(ExpertConsultation $consultation)
    {
        $consultation->delete();
        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
