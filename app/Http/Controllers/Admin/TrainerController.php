<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Cache;
use App\Models\Trainer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class TrainerController extends Controller
{
    public function index()
    {
        $trainers = Trainer::orderBy('order')->get()->map(function($trainer) {
            return $this->formatTrainerResponse($trainer);
        });

        return response()->json($trainers);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:100',
            'name_en' => 'nullable|string|max:100',
            'bio_ar' => 'nullable|string',
            'bio_en' => 'nullable|string',
            'image' => 'required|image|max:2048',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $image = Image::read($request->file('image'));
        $image->cover(400, 400);
        $filename = uniqid() . '.jpg';
        $image->save(storage_path('app/public/trainers/' . $filename), quality: 85);

        $trainer = Trainer::create([
            'name' => ['ar' => $data['name_ar'], 'en' => $data['name_en'] ?? ''],
            'bio' => ['ar' => $data['bio_ar'] ?? '', 'en' => $data['bio_en'] ?? ''],
            'image' => 'trainers/' . $filename,
            'order' => $data['order'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
        ]);

        Cache::forget('trainers:all');
        return response()->json($this->formatTrainerResponse($trainer), 201);
    }

    public function show(Trainer $trainer)
    {
        return response()->json($this->formatTrainerResponse($trainer));
    }

    public function update(Request $request, Trainer $trainer)
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:100',
            'name_en' => 'nullable|string|max:100',
            'bio_ar' => 'nullable|string',
            'bio_en' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $imagePath = $trainer->image;

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($trainer->image);
            $image = Image::read($request->file('image'));
            $image->cover(400, 400);
            $filename = uniqid() . '.jpg';
            $image->save(storage_path('app/public/trainers/' . $filename), quality: 85);
            $imagePath = 'trainers/' . $filename;
        }

        $trainer->update([
            'name' => ['ar' => $data['name_ar'], 'en' => $data['name_en'] ?? ''],
            'bio' => ['ar' => $data['bio_ar'] ?? '', 'en' => $data['bio_en'] ?? ''],
            'image' => $imagePath,
            'order' => $data['order'] ?? $trainer->order,
            'is_active' => $data['is_active'] ?? $trainer->is_active,
        ]);

        Cache::forget('trainers:all');
        return response()->json($this->formatTrainerResponse($trainer));
    }

    public function destroy(Trainer $trainer)
    {
        Storage::disk('public')->delete($trainer->image);
        $trainer->delete();
        Cache::forget('trainers:all');
        return response()->json(['message' => 'تم الحذف بنجاح']);
    }

    /**
     * تنسيق البيانات وإضافة URL كامل للصورة
     */
    private function formatTrainerResponse(Trainer $trainer)
    {
        return [
            'id' => $trainer->id,
            'name' => $trainer->name,
            'bio' => $trainer->bio,
            'image' => $trainer->image ? asset('storage/' . $trainer->image) : null,
            'order' => $trainer->order,
            'is_active' => $trainer->is_active,
            'created_at' => $trainer->created_at,
            'updated_at' => $trainer->updated_at,
        ];
    }
}
