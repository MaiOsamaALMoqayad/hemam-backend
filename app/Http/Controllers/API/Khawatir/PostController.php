<?php

namespace App\Http\Controllers\Api\Khawatir;

use App\Http\Controllers\Controller;
use App\Models\KhaterPost;
use App\Http\Resources\KhaterPostResource;

class PostController extends Controller
{
    public function index($categoryId)
    {
        $posts = KhaterPost::where('khater_category_id', $categoryId)
            ->where('is_published', true)
            ->with('images') 
            ->latest()
            ->get();

        return KhaterPostResource::collection($posts);
    }

    public function show($id)
    {
        $post = KhaterPost::where('id', $id)
            ->where('is_published', true)
            ->with('images')
            ->firstOrFail();

        return new KhaterPostResource($post);
    }
}
