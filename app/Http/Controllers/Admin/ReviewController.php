<?php

namespace App\Http\Controllers\Admin;

use App\Models\Review;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::with('activity')->latest()->get();

        return ReviewResource::collection($reviews);
    }

    public function approve($id)
    {
        $review = Review::findOrFail($id);

        $review->update([
            'is_published' => !$review->is_published
        ]);

        return response()->json([
            'message' => $review->is_published ? 'تم قبول التقييم' : 'تم إلغاء قبول التقييم',
            'status'  => $review->is_published
        ]);
    }

    public function destroy($id)
    {
        Review::findOrFail($id)->delete();

        return response()->json([
            'message' => 'تم حذف التقييم بنجاح'
        ]);
    }
}
