<?php
namespace App\Http\Controllers\Admin;

use App\Models\MapLocation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MapLocationController extends Controller
{
    public function index()
    {
        return MapLocation::latest()->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'description' => 'nullable|string',
        ]);

        return MapLocation::create($data);
    }

    public function show($id)
    {
        return MapLocation::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $item = MapLocation::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'description' => 'nullable|string',
        ]);

        $item->update($data);

        return $item;
    }

    public function destroy($id)
    {
        MapLocation::findOrFail($id)->delete();
        return response()->json(['message' => 'تم الحذف']);
    }
}
