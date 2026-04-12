<?php
namespace App\Http\Controllers\Api;

use App\Models\News;
use App\Http\Controllers\Controller;

class NewsController extends Controller
{
    public function index()
    {
        return response()->json(
            News::select('id', 'title', 'image')->latest()->get()
        );
    }

    public function show($id)
    {
        return response()->json(News::findOrFail($id));
    }
}
