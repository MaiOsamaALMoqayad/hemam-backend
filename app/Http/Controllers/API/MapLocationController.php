<?php

namespace App\Http\Controllers\Api;

use App\Models\MapLocation;
use App\Http\Controllers\Controller;

class MapLocationController extends Controller
{
    public function index()
    {
        return response()->json(MapLocation::all());
    }
}

