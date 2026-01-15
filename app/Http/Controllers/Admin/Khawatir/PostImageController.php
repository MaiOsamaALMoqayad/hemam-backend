<?php

namespace App\Http\Controllers\Admin\Khawatir;

use App\Models\KhaterPost;
use Illuminate\Http\Request;
use App\Models\KhaterPostImage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\KhaterPostImageResource;

class PostImageController extends Controller
{
    public function store(Request $request, KhaterPost $post)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'required|image|max:2048',
        ]);

        $images = [];

        foreach ($request->file('images') as $image) {
            // تخزين الصورة
            $path = $image->store('khawatir/posts', 'public');

            $newImage = $post->images()->create([
                'image_path' => $path
            ]);

            $images[] = $newImage;
        }

        return response()->json([
            'message' => 'Images uploaded successfully',
            'data' => KhaterPostImageResource::collection($images) // إرجاع مصفوفة الصور
        ], 201);
    }


public function destroy($id)
{
    $image = \App\Models\KhaterPostImage::findOrFail($id);

    if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
        Storage::disk('public')->delete($image->image_path);
    }

    $image->delete();

    return response()->json([
        'status' => 'success',
        'message' => 'تم حذف الصورة بنجاح'
    ], 200);
}
}
