<?php

namespace App\Http\Controllers\Api;

use App\Models\Review;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'person_name' => 'required|string|max:255',
            'activity_id' => 'required|exists:activities,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $data['is_published'] = false;

        $review = Review::create($data);

        return response()->json([
            'message' => 'تم إرسال التقييم وسيتم مراجعته من الإدارة',
            'data' => new ReviewResource($review)
        ], 201);
    }
    public function index()
{
    return ReviewResource::collection(
        Review::where('is_published', true)
            ->latest()
            ->get()
    );
}
public function activityReviews($activityId)
{
    $reviews = Review::where('activity_id', $activityId)
        ->where('is_published', true)
        ->inRandomOrder()
        ->take(3)
        ->get();

    return ReviewResource::collection($reviews);
}

}
