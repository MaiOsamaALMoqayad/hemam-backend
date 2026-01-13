<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Log;
use App\Models\ContactMessage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\ContactStoreRequest;
use Illuminate\Support\Facades\Notification;

class ContactController extends Controller
{
    public function store(ContactStoreRequest $request)
    {
        try {
            $identifier = $request->email;
            $cacheKey = "contact:throttle:{$identifier}";

            if (Cache::has($cacheKey)) {
                return response()->json([
                    'success' => false,
                    'message' => 'لقد أرسلت رسالة مؤخرًا. يرجى الانتظار قليلاً قبل الإرسال مرة أخرى.'
                ], 429);
            }

            // تحقق من وجود رسالة بنفس المحتوى في آخر 10 دقائق
            $exists = ContactMessage::where('email', $request->email)
                ->where('message', $request->message)
                ->where('created_at', '>=', now()->subMinutes(10))
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'لقد أرسلت هذه الرسالة بالفعل مؤخراً.'
                ], 429);
            }

            // حفظ الرسالة
            $contact = ContactMessage::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'subject' => $request->subject,
                'message' => $request->message,
                'is_read' => false,
            ]);

            // ضع مفتاح Throttle في الكاش لمدة 5 دقائق
            Cache::put($cacheKey, true, now()->addMinutes(5));
            // إرسال إشعار للأدمن عبر Email
            try {
                Log::info('Starting to send email...');
                Notification::route('mail', env('ADMIN_EMAIL'))
                    ->notify(new \App\Notifications\NewContactMessageNotification($contact));

                Log::info('Email sent successfully!');
            } catch (\Exception $e) {
                Log::warning('Failed sending email: ' . $e->getMessage());
            }

            Log::info('New contact message received', [
                'id' => $contact->id,
                'name' => $contact->name,
                'email' => $contact->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال رسالتك بنجاح. سنتواصل معك قريباً',
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Contact Form Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال رسالتك. يرجى المحاولة مرة أخرى',
            ], 500);
        }
    }
}
