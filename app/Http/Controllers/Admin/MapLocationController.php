<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MapLocation;
use App\Models\MapLocationImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MapLocationController extends Controller
{
    public function index()
    {
        return MapLocation::with('images')->latest()->get();
    }

   public function store(Request $request)
{
    $request->validate([
        'name'      => 'required|string|max:255',
        'latitude'  => 'required|numeric',
        'longitude' => 'required|numeric',
        'description' => 'nullable|string',
        'images'    => 'nullable|array',
        'images.*'  => 'image|mimes:jpeg,png,jpg,webp|max:3072'
    ]);

    return DB::transaction(function () use ($request) {

        $location = MapLocation::create($request->only(['name', 'latitude', 'longitude', 'description']));

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('map_locations', 'public');

                $location->images()->create([
                    'image' => $path
                ]);
            }
        }

        return response()->json([
            'message' => 'تم إنشاء الموقع وصوره بنجاح',
            'data' => $location->load('images')
        ], 201);
    });
}

    public function update(Request $request, $id)
    {
        $location = MapLocation::findOrFail($id);

        $location->update($request->only(['name', 'latitude', 'longitude', 'description']));

        if ($request->hasFile('images')) {
            $this->uploadImages($request->file('images'), $location);
        }

        return response()->json([
            'message' => 'تم تحديث الموقع بنجاح',
            'data' => $location->load('images')
        ]);
    }

    public function deleteImage($id)
    {
        $image = MapLocationImage::findOrFail($id);
        Storage::disk('public')->delete($image->image);
        $image->delete();

        return response()->json(['message' => 'تم حذف الصورة بنجاح']);
    }

    public function destroy($id)
    {
        $location = MapLocation::findOrFail($id);

        foreach ($location->images as $img) {
            Storage::disk('public')->delete($img->image);
        }

        $location->delete();
        return response()->json(['message' => 'تم حذف الموقع وجميع صوره']);
    }

    private function uploadImages($files, $location)
    {
        foreach ($files as $image) {
            $path = $image->store('map_locations', 'public');
            $location->images()->create(['image' => $path]);
        }
    }
}
