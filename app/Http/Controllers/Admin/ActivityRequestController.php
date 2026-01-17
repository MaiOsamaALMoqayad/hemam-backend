<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\ActivityRequest;
use App\Http\Controllers\Controller;

class ActivityRequestController extends Controller
{
    // عرض كل الطلبات + فلترة حسب النشاط + المقروء/غير المقروء
public function index(Request $request)
{
    $query = ActivityRequest::with(['activity' => function ($q) {
        $q->select('id', 'title'); // فقط رقم واسم النشاط
    }]);

    // فلترة حسب النشاط
    if ($request->filled('activity_id')) {
        $query->where('activity_id', $request->activity_id);
    }

    // فلترة حسب الحالة: read / unread
    if ($request->filled('status')) {
        $query->where('is_read', $request->status === 'read');
    }

    return response()->json($query->latest()->get());
}


    // عرض طلب واحد
public function show($id)
{
    $item = ActivityRequest::with(['activity' => function ($q) {
        $q->select('id'); // بس الرقم
    }])->findOrFail($id);

    return response()->json($item);
}



    // تحديد الطلب كمقروء
    public function markAsRead($id)
    {
        $item = ActivityRequest::findOrFail($id);
        $item->update(['is_read' => true]);

        return response()->json(['message' => 'تم تحديد الطلب كمقروء']);
    }

    // حذف الطلب
    public function destroy($id)
    {
        ActivityRequest::findOrFail($id)->delete();

        return response()->json(['message' => 'تم حذف الطلب بنجاح']);
    }
}
