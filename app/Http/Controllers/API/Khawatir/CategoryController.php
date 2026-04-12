<?php

namespace App\Http\Controllers\API\Khawatir;

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

