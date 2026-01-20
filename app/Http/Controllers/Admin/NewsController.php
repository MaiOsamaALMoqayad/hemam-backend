<?php

namespace App\Http\Controllers\Admin;

use App\Models\News;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

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
            'image' => 'required|image',
            'description' => 'required|string',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|string|max:1000',
        ]);

        $data['image'] = $request->file('image')->store('news', 'public');

        $news = News::create($data);

        return response()->json($news, 201);
    }

    public function show($id)
    {
        return response()->json(News::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $news = News::findOrFail($id);

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'image' => 'sometimes|image',
            'description' => 'sometimes|string',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($news->image);
            $data['image'] = $request->file('image')->store('news', 'public');
        }

        $news->update($data);

        return response()->json($news);
    }

    public function destroy($id)
    {
        $news = News::findOrFail($id);
        Storage::disk('public')->delete($news->image);
        $news->delete();

        return response()->json(['message' => 'تم حذف الخبر بنجاح']);
    }
}
