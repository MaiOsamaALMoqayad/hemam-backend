<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainerApplication;
use Illuminate\Http\Request;

class TrainerApplicationController extends Controller
{
   
    private function qualificationMap()
    {
        return [
            'high_school' => 'ثانوية عامة',
            'bachelor'    => 'بكالوريوس',
            'master'      => 'ماجستير',
            'other'       => 'أخرى',
        ];
    }


    private function trainingFieldsMap()
    {
        return [
            'leadership'            => 'القيادة',
            'management'            => 'الإدارة',
            'education'             => 'التعليم',
            'guidance'              => 'الإرشاد',
            'odt_activities'        => 'أنشطة ODT',
            'psychological_support' => 'الدعم النفسي',
            'sharia_sciences'       => 'العلوم الشرعية',
            'other'                 => 'أخرى',
        ];
    }

    /**
     * عرض جميع طلبات التدريب
     */
    public function index(Request $request)
    {
        $query = TrainerApplication::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $applications = $query->latest()->get();

        $applications->transform(function ($app) {

            // ترجمة الشهادة العلمية
            $app->qualification =
                $this->qualificationMap()[$app->qualification]
                ?? $app->qualification;

            // ترجمة مجالات التدريب
            $app->training_fields = collect($app->training_fields)->map(function ($field) use ($app) {
                if ($field === 'other') {
                    return $app->training_field_other; // نص المجال الآخر إذا موجود
                }

                return $this->trainingFieldsMap()[$field] ?? $field;
            })->values();

            return $app;
        });

        return response()->json($applications);
    }

    /**
     * عرض طلب تدريب واحد
     */
    public function show(TrainerApplication $trainerApplication)
    {
        // ترجمة الشهادة العلمية
        $trainerApplication->qualification =
            $this->qualificationMap()[$trainerApplication->qualification]
            ?? $trainerApplication->qualification;

        // ترجمة مجالات التدريب
        $trainerApplication->training_fields = collect($trainerApplication->training_fields)->map(function ($field) use ($trainerApplication) {
            if ($field === 'other') {
                return $trainerApplication->training_field_other; // نص المجال الآخر إذا موجود
            }

            return $this->trainingFieldsMap()[$field] ?? $field;
        })->values();

        return response()->json($trainerApplication);
    }

    /**
     * تحديث حالة الطلب
     */
    public function updateStatus(Request $request, TrainerApplication $trainerApplication)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'admin_notes' => 'nullable|string',
        ]);

        $trainerApplication->update($data);

        return response()->json([
            'message' => 'تم التحديث',
            'application' => $trainerApplication
        ]);
    }

    /**
     * حذف طلب تدريب
     */
    public function destroy(TrainerApplication $trainerApplication)
    {
        $trainerApplication->delete();
        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
