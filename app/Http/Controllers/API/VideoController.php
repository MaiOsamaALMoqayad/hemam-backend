<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function show()
    {
        $video = Video::first();

        if (!$video) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد فيديو مضاف حالياً.',
                'data'    => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب الفيديو بنجاح.',
            'data'    => [
                'video_url' => $video->video_url
            ]
        ], 200);
    }
}
