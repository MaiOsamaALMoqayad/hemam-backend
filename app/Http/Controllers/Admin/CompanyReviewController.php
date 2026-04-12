<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyReview;
use Illuminate\Http\Request;

class CompanyReviewController extends Controller
{
     // أدمن: عرض الكل
    public function index()
    {
        return CompanyReview::latest()->get();
    }

    // قبول
    public function approve($id)
    {
        $review = CompanyReview::findOrFail($id);

        $review->update([
            'is_published' => true
        ]);

        return response()->json([
            'message' => 'Approved successfully'
        ]);
    }

    // حذف
    public function destroy($id)
    {
        CompanyReview::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Deleted successfully'
        ]);
    }
}

