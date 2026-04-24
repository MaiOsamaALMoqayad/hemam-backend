<?php

namespace App\Http\Controllers\Admin;

use App\Models\News;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class NewsController extends Controller
{
    public function index()
    {
        return response()->json(News::latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // تحديد النوع والحجم للحماية
            'description' => 'required|string',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadAndResize($request->file('image'));
        }

        $news = News::create($data);
        return response()->json($news, 201);
    }

    public function update(Request $request, $id)
    {
        $news = News::findOrFail($id);

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'image' => 'sometimes|image|max:2048',
            'description' => 'sometimes|string',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($news->image);
            $data['image'] = $this->uploadAndResize($request->file('image'));
        }

        $news->update($data);
        return response()->json($news);
    }

    protected function uploadAndResize($file)
    {
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = 'news/' . $filename;

        $image = Image::read($file);
        $image->cover(800, 500);

        Storage::disk('public')->put($path, (string) $image->encode());

        return $path;
    }

    public function destroy($id)
    {
        $news = News::findOrFail($id);
        Storage::disk('public')->delete($news->image);
        $news->delete();

        return response()->json(['message' => 'تم حذف الخبر بنجاح']);
    }
}
