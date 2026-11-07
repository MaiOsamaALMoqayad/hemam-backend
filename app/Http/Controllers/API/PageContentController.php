<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PageContent;

class PageContentController extends Controller
{
    /**
     * GET /v1/content — every saved value as {key: value}.
     * Text is a string, a link is {label, url}, an image is its URL.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data'    => (object) PageContent::publicValues(),
        ]);
    }
}
