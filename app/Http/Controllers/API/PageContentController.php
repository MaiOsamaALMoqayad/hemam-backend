<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PageContent;

class PageContentController extends Controller
{
    /**
     * GET /v1/content — every saved value as {key: value}.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data'    => (object) PageContent::allValues(),
        ]);
    }
}
