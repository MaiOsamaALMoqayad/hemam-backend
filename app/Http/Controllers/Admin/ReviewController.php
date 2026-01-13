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

public function approve($id)
{
    $review = Review::findOrFail($id);
    $review->update(['is_published' => true]);

    return response()->json([
        'message' => 'تم قبول التقييم'
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
