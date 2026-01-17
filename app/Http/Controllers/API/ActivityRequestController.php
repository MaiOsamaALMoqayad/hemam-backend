<?php
namespace App\Http\Controllers\API;

use App\Models\ActivityRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Notification;
use App\Http\Requests\ActivityRequestStoreRequest;

class ActivityRequestController extends Controller
{
    public function store(ActivityRequestStoreRequest $request)
    {
        try {
            $identifier = $request->phone;
            $cacheKey = "activity_request:throttle:{$identifier}";

            if (Cache::has($cacheKey)) {
                return response()->json([
                    'success' => false,
                    'message' => 'لقد أرسلت طلبًا مؤخرًا. يرجى الانتظار قليلاً.'
                ], 429);
            }

            // منع التكرار لنفس النشاط خلال 10 دقائق
            $exists = ActivityRequest::where('phone', $request->phone)
                ->where('activity_id', $request->activity_id)
                ->where('created_at', '>=', now()->subMinutes(10))
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'لقد أرسلت طلبًا لهذا النشاط مؤخرًا.'
                ], 429);
            }

            $item = ActivityRequest::create([
                'name' => $request->name,
                'age' => $request->age,
                'phone' => $request->phone,
                'gender' => $request->gender,
                'activity_id' => $request->activity_id,
                'is_read' => false,
            ]);

            // Throttle 5 دقائق
            Cache::put($cacheKey, true, now()->addMinutes(5));

            // إرسال إشعار للأدمن
            try {
                Log::info('Starting to send activity request email...');
                $item->load('activity');
                Notification::route('mail', env('ADMIN_EMAIL'))
                    ->notify(new \App\Notifications\NewActivityRequestNotification($item));
                Log::info('Activity request email sent!');
            } catch (\Exception $e) {
                Log::warning('Failed sending activity request email: ' . $e->getMessage());
            }

            Log::info('New activity join request received', [
                'id' => $item->id,
                'name' => $item->name,
                'activity_id' => $item->activity_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال طلب الانضمام بنجاح',
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Activity Request Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال الطلب. يرجى المحاولة مرة أخرى',
            ], 500);
        }
    }
}
