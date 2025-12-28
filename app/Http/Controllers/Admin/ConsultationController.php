<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpertConsultation;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    private function consultationTypeAr($type)
    {
        return [
            'educational' => 'تعليمية',
            'management'  => 'إدارية',
            'leadership'  => 'قيادية',
            'personal'    => 'شخصية',
        ][$type] ?? $type;
    }

    public function index(Request $request)
    {
        $query = ExpertConsultation::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $consultations = $query->latest()->get()->map(function ($consultation) {
            $consultation->consultation_type =
                $this->consultationTypeAr($consultation->consultation_type);
            return $consultation;
        });

        return response()->json($consultations);
    }

    public function show(ExpertConsultation $consultation)
    {
        $consultation->consultation_type =
            $this->consultationTypeAr($consultation->consultation_type);

        return response()->json($consultation);
    }

    public function updateStatus(Request $request, ExpertConsultation $consultation)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
            'admin_notes' => 'nullable|string',
        ]);

        $consultation->update($data);

        // تحويل النوع للعربي قبل الإرجاع
        $consultation->consultation_type =
            $this->consultationTypeAr($consultation->consultation_type);

        return response()->json([
            'message' => 'تم التحديث',
            'consultation' => $consultation
        ]);
    }

    public function destroy(ExpertConsultation $consultation)
    {
        $consultation->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
