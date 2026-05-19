<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function index()
    {
        $partners = Partner::orderBy('order_index', 'asc')->get();

        return response()->json([
            'success' => true,
            'data'    => PartnerResource::collection($partners)
        ], 200);
    }

}
