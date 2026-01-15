<?php

namespace App\Http\Controllers\Api\Khawatir;

use App\Models\KhaterCategory;
use App\Http\Controllers\Controller;
use App\Http\Resources\KhaterCategoryResource;

class CategoryController extends Controller
{
    public function index()
    {
       return KhaterCategoryResource::collection(
    KhaterCategory::where('is_active', true)->get()
);
    }
}

