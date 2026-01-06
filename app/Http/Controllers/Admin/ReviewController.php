<?php

namespace App\Http\Controllers\Admin;

use App\Models\Review;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Http\Requests\Admin\ReviewRequest;



class ReviewController extends Controller
{
    public function index()
    {
        return ReviewResource::collection(
            Review::latest()->get()
        );
    }

    public function store(ReviewRequest $request)
    {
        $review = Review::create($request->validated());

        return response()->json([
            'message' => 'تم إضافة التقييم بنجاح',
            'data'    => new ReviewResource($review),
        ], 201);
    }

    public function update(ReviewRequest $request, $id)
    {
        $review = Review::findOrFail($id);
        $review->update($request->validated());

        return response()->json([
            'message' => 'تم تعديل التقييم بنجاح',
            'data'    => new ReviewResource($review),
        ]);
    }

    public function destroy($id)
    {
        Review::findOrFail($id)->delete();

        return response()->json([
            'message' => 'تم حذف التقييم'
        ]);
    }
}
