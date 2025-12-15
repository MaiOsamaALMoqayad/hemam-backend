<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Log, Cookie};
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login with HttpOnly Cookie
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ], [
                'email.required' => 'البريد الإلكتروني مطلوب',
                'email.email' => 'البريد الإلكتروني غير صحيح',
                'password.required' => 'كلمة المرور مطلوبة',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['البريد الإلكتروني أو كلمة المرور غير صحيحة'],
                ]);
            }

            // حذف التوكنات القديمة
            $user->tokens()->delete();

            // إنشاء توكن جديد
            $token = $user->createToken('admin-token')->plainTextToken;

            Log::info('Admin logged in', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            // إنشاء HttpOnly Cookie
            $cookie = cookie(
                'admin_token',              // اسم الـ Cookie
                $token,                     // القيمة
                60 * 24 * 7,               // 7 أيام
                '/',                        // Path
                null,                       // Domain (null = current domain)
                false,                       // Secure (HTTPS only)
                true,                       // HttpOnly (JavaScript can't access)
                false,                      // Raw
                'strict'                    // SameSite
            );

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الدخول بنجاح',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ])->cookie($cookie);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في البيانات',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Admin Login Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تسجيل الدخول',
            ], 500);
        }
    }

    /**
     * Logout and clear cookie
     */
    public function logout(Request $request)
    {
        try {
            // حذف التوكن الحالي
            $request->user()->currentAccessToken()->delete();

            Log::info('Admin logged out', [
                'user_id' => $request->user()->id,
            ]);

            // حذف الـ Cookie
            $cookie = Cookie::forget('admin_token');

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الخروج بنجاح'
            ])->cookie($cookie);

        } catch (\Exception $e) {
            Log::error('Admin Logout Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تسجيل الخروج',
            ], 500);
        }
    }

    /**
     * Get current user
     */
    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ],
        ]);
    }

    /**
     * Check authentication status
     */
    public function check(Request $request)
    {
        return response()->json([
            'authenticated' => $request->user() !== null,
            'user' => $request->user() ? [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ] : null,
        ]);
    }
}
