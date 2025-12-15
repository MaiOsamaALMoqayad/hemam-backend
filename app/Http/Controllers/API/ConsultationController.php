<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConsultationRequest;
use App\Models\ExpertConsultation;
use App\Notifications\NewConsultationNotification;
use Illuminate\Support\Facades\{Notification, Log, DB};

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
     *   "message": "تم تقديم طلب الاستشارة بنجاح"
     * }
     */
    public function store(ConsultationRequest $request)
    {
        DB::beginTransaction();

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

            // Log النجاح
            Log::info('New consultation request received', [
                'id' => $consultation->id,
                'name' => $consultation->name,
                'type' => $consultation->consultation_type,
                'ip' => $request->ip(),
            ]);

            // إرسال إشعار للأدمن عبر Email
            try {
                $adminEmail = config('mail.admin_email', env('ADMIN_EMAIL', 'admin@hemam.com'));

                Notification::route('mail', $adminEmail)
                    ->notify(new NewConsultationNotification($consultation));

                Log::info('Consultation email sent successfully to: ' . $adminEmail, [
                    'consultation_id' => $consultation->id
                ]);

            } catch (\Exception $emailError) {
                // لو فشل الإيميل، نسجل الخطأ لكن ما نوقف العملية
                Log::warning('Failed to send consultation email notification', [
                    'consultation_id' => $consultation->id,
                    'error' => $emailError->getMessage(),
                    'line' => $emailError->getLine(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تقديم طلب الاستشارة بنجاح. سيتواصل معك أحد خبرائنا قريباً',
                'consultation_id' => $consultation->id,
            ], 201);

        } catch (\Illuminate\Database\QueryException $dbError) {
            DB::rollBack();

            Log::error('Database error in Consultation Form', [
                'error' => $dbError->getMessage(),
                'code' => $dbError->getCode(),
                'line' => $dbError->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في قاعدة البيانات. يرجى المحاولة مرة أخرى',
            ], 500);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Consultation Form Error', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تقديم طلب الاستشارة. يرجى المحاولة مرة أخرى',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
