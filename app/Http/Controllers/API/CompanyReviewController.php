<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CompanyReview;

class CompanyReviewController extends Controller
{
    // ➤ إنشاء تقييم (للزائر)
    public function store(Request $request)
    {
        $request->validate([
            'person_name' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $review = CompanyReview::create([
            'person_name' => $request->person_name,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_published' => false,
        ]);

        return response()->json([
            'message' => 'Review submitted successfully and waiting for approval',
            'data' => $review
        ]);
    }

    //  عرض التقييمات للموقع (المقبولة فقط)
    public function index()
    {
        $reviews = CompanyReview::where('is_published', true)
            ->latest()
            ->get();

        return response()->json($reviews);
    }

 
}
