<?php

namespace App\Http\Controllers\Admin\Khawatir;

use App\Http\Controllers\Controller;
use App\Models\KhaterCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        return KhaterCategory::latest()->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);

        return KhaterCategory::create($data);
    }

    public function update(Request $request, KhaterCategory $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);


        $category->update($data);

        return $category;
    }

    public function destroy(KhaterCategory $category)
    {
        $category->delete();
        return response()->noContent();
    }
}

