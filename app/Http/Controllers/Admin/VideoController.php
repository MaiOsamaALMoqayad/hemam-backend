<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VideoController extends Controller
{
    public function index()
    {
        $video = Video::first();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب الفيديو الحالي للأدمن.',
            'data'    => $video
        ], 200);
    }

    /**
     * إضافة أو تحديث الفيديو الوحيد
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'video_url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        // تحديث السجل رقم 1 دائماً لضمان بقاء فيديو واحد فقط
        $video = Video::updateOrCreate(
            ['id' => 1],
            ['video_url' => $request->video_url]
        );

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ وتحديث الفيديو بنجاح.',
            'data'    => $video
        ], 200);
    }
    public function destroy()
    {
        // جلب الفيديو الوحيد وحذفه
        $video = Video::first();

        if (!$video) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد فيديو لحذفه.'
            ], 404);
        }

        $video->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الفيديو بنجاح من السيرفر.'
        ], 200);
    }
}
