<?php

namespace App\Http\Controllers\API;

use App\Models\TrainerApplication;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Notification;
use App\Http\Requests\TrainerApplicationRequest;
use App\Notifications\NewTrainerApplicationNotification;

class TrainerApplicationController extends Controller
{
    /**
     * Store a new trainer application.
     *
     * POST /api/v1/trainer-applications
     *
     * Body:
     * {
     *   "full_name": "أحمد محمد علي",
     *   "age": 28,
     *   "phone": "599123456",
     *   "email": "ahmad@example.com",
     *   "residence": "رام الله",
     *   "gender": "male",
     *   "qualification": "bachelor",
     *   "qualification_other": null,
     *   "specialization": "تكنولوجيا المعلومات",
     *   "experience_years": 5,
     *   "program_name": "برنامج القيادة الشبابية",
     *   "social_links": {
     *     "facebook": "facebook.com/ahmad",
     *     "instagram": "@ahmad"
     *   },
     *   "has_previous_courses": true,
     *   "courses_description": "دورة في القيادة...",
     *   "course_outcomes": "تطوير مهارات القيادة...",
     *   "about_me": "أنا مدرب شغوف...",
     *   "training_fields": ["leadership", "management"],
     *   "training_field_other": null
     * }
     *
     * Response:
     * {
     *   "success": true,
     *   "message": "تم تقديم طلبك بنجاح. سنراجعه وسنتواصل معك قريباً"
     * }
     */
    public function store(TrainerApplicationRequest $request)
    {
        try {
            // حفظ الطلب في قاعدة البيانات
            $application = TrainerApplication::create([
                'full_name' => $request->full_name,
                'age' => $request->age,
                'phone' => $request->phone,
                'email' => $request->email,
                'residence' => $request->residence,
                'gender' => $request->gender,
                'qualification' => $request->qualification,
                'qualification_other' => $request->qualification_other,
                'specialization' => $request->specialization,
                'experience_years' => $request->experience_years,
                'program_name' => $request->program_name,
                'social_links' => $request->social_links,
                'has_previous_courses' => $request->has_previous_courses,
                'courses_description' => $request->courses_description,
                'course_outcomes' => $request->course_outcomes,
                'about_me' => $request->about_me,
                'training_fields' => $request->training_fields,
                'training_field_other' => $request->training_field_other,
                'status' => 'pending',
            ]);

                  try {
                $adminEmail = env('ADMIN_EMAIL', 'admin@hemam.com');
                Notification::route('mail', $adminEmail)
                    ->notify(new NewTrainerApplicationNotification($application));

                Log::info('Trainer application email sent successfully to: ' . $adminEmail);
            } catch (\Exception $e) {
                Log::warning('Failed to send trainer application email: ' . $e->getMessage());
            }

            // Log للمراجعة
            Log::info('New trainer application received', [
                'id' => $application->id,
                'full_name' => $application->full_name,
                'email' => $application->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تقديم طلبك بنجاح. سنراجعه وسنتواصل معك قريباً',
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Trainer Application Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تقديم طلبك. يرجى المحاولة مرة أخرى',
            ], 500);
        }
    }
}
