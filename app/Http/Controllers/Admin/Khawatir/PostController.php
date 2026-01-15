<?php

namespace App\Http\Controllers\Admin\Khawatir;

use App\Http\Controllers\Controller;
use App\Models\KhaterPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
  public function index(Request $request)
{
    $categoryId = $request->query('category_id');

    $query = KhaterPost::with('category');

    if ($categoryId) {
        $query->where('khater_category_id', $categoryId);
    }

    return $query->latest()->get();
}

    public function store(Request $request)
    {
        $data = $request->validate([
            'khater_category_id' => 'required|exists:khater_categories,id',
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'is_published' => 'boolean',
        ]);

        $data['published_at'] = $data['is_published'] ? now() : null;

        return KhaterPost::create($data);
    }

    public function update(Request $request, KhaterPost $post)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'is_published' => 'boolean',
        ]);

        $data['published_at'] = $data['is_published'] ? now() : null;

        $post->update($data);

        return $post;
    }

  public function destroy(KhaterPost $post)
    {
        $post->delete();
        return response()->json(['message' => 'تم حذف المقال بنجاح']);
    }
}

