<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $query = ContactMessage::query();

        if ($request->has('status')) {
            $query->where('is_read', $request->status === 'read');
        }

        return response()->json($query->latest()->get());
    }

    public function show(ContactMessage $contact)
    {
        return response()->json($contact);
    }

    public function markAsRead(ContactMessage $contact)
    {
        $contact->update(['is_read' => true]);
        return response()->json(['message' => 'تم التحديث']);
    }

    public function destroy(ContactMessage $contact)
    {
        $contact->delete();
        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
