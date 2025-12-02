<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Notification;
use App\Models\ExpertConsultation;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\ConsultationRequest;

class ConsultationController extends Controller
{
    /**
     * Store a new expert consultation request.
     *
     * POST /api/v1/consultations
     *
     * Body:
     * {
     *   "name": "أحمد محمد",
     *   "whatsapp": "+970599123456",
     *   "consultation_type": "educational",
     *   "consultation_details": "أرغب في استشارة حول...",
     *   "notes": "ملاحظات إضافية"
     * }
     *
     * Response:
     * {
     *   "success": true,
     *   "message": "تم تقديم طلب الاستشارة بنجاح. سيتواصل معك أحد خبرائنا قريباً"
     * }
     */
    public function store(ConsultationRequest $request)
    {
        try {
            // حفظ طلب الاستشارة في قاعدة البيانات
            $consultation = ExpertConsultation::create([
                'name' => $request->name,
                'whatsapp' => $request->whatsapp,
                'consultation_type' => $request->consultation_type,
                'consultation_details' => $request->consultation_details,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

             try {
                Notification::route('mail', env('ADMIN_EMAIL', 'admin@hemam.com'))
                    ->notify(new \App\Notifications\NewConsultationNotification($consultation));
            } catch (\Exception $e) {
                Log::warning('Failed to send email notification: ' . $e->getMessage());
            }
            Log::info('New consultation request received', [
                'id' => $consultation->id,
                'name' => $consultation->name,
                'type' => $consultation->consultation_type,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تقديم طلب الاستشارة بنجاح. سيتواصل معك أحد خبرائنا قريباً',
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Consultation Form Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تقديم طلب الاستشارة. يرجى المحاولة مرة أخرى',
            ], 500);
        }
    }
}
