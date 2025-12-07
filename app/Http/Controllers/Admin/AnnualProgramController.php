<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\AnnualProgram;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class AnnualProgramController extends Controller
{
    public function index()
    {
        $programs = AnnualProgram::orderBy('order')->get()->map(function ($program) {
            $program->image = $program->image ? asset('storage/' . $program->image) : null;
            return $program;
        });

        return response()->json($programs);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title_ar' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'description_ar' => 'required|string',
            'description_en' => 'nullable|string',
            'image' => 'required|image|max:2048',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            $image = Image::read($request->file('image'));
            $image->cover(800, 600);
            $filename = uniqid() . '.jpg';
            $image->save(storage_path('app/public/annual_programs/' . $filename), quality: 85);
            $imagePath = 'annual_programs/' . $filename;
        }

        $program = AnnualProgram::create([
            'title' => ['ar' => $data['title_ar'], 'en' => $data['title_en'] ?? ''],
            'description' => ['ar' => $data['description_ar'], 'en' => $data['description_en'] ?? ''],
            'image' => $imagePath,
            'order' => $data['order'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
        ]);

        Cache::forget('annual_programs:all');

        $program->image = $program->image ? asset('storage/' . $program->image) : null;

        return response()->json($program, 201);
    }

    public function show(AnnualProgram $annualProgram)
    {
        $annualProgram->image = $annualProgram->image ? asset('storage/' . $annualProgram->image) : null;
        return response()->json($annualProgram);
    }

  public function update(Request $request, AnnualProgram $annualProgram)
{
    // Validate incoming request
    $data = $request->validate([
        'title_ar' => 'required|string|max:255',
        'title_en' => 'nullable|string|max:255',
        'description_ar' => 'required|string',
        'description_en' => 'nullable|string',
        'image' => 'nullable|image|max:2048',
        'order' => 'nullable|integer',
        'is_active' => 'nullable|boolean',
    ]);

    $imagePath = $annualProgram->image; // Default to existing image

    if ($request->hasFile('image')) {
        try {
            // Log the uploaded file info
            Log::info('Updating AnnualProgram image', [
                'file_name' => $request->file('image')->getClientOriginalName(),
                'real_path' => $request->file('image')->getRealPath(),
                'program_id' => $annualProgram->id,
            ]);

            // Delete old image if exists
            if ($annualProgram->image && Storage::disk('public')->exists($annualProgram->image)) {
                Storage::disk('public')->delete($annualProgram->image);
                Log::info('Deleted old image', ['old_image' => $annualProgram->image]);
            }

            // Process new image
            $image = Image::read($request->file('image'));
            $image->cover(800, 600);
            $filename = uniqid() . '.jpg';
            $savePath = storage_path('app/public/annual_programs/' . $filename);
            $image->save($savePath, 85);

            $imagePath = 'annual_programs/' . $filename;
            Log::info('Saved new image', ['new_image' => $imagePath]);
        } catch (\Exception $e) {
            Log::error('Failed to update image', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'حدث خطأ أثناء رفع الصورة',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Update program data
    $annualProgram->update([
        'title' => ['ar' => $data['title_ar'], 'en' => $data['title_en'] ?? ''],
        'description' => ['ar' => $data['description_ar'], 'en' => $data['description_en'] ?? ''],
        'image' => $imagePath,
        'order' => $data['order'] ?? $annualProgram->order,
        'is_active' => $data['is_active'] ?? $annualProgram->is_active,
    ]);

    // Clear cache
    Cache::forget('annual_programs:all');

    // Convert image path to full URL
    $annualProgram->image = $annualProgram->image ? asset('storage/' . $annualProgram->image) : null;

    Log::info('AnnualProgram updated successfully', [
        'program_id' => $annualProgram->id,
        'image_url' => $annualProgram->image,
    ]);

    return response()->json($annualProgram);
}


    public function destroy(AnnualProgram $annualProgram)
    {
        if ($annualProgram->image) {
            Storage::disk('public')->delete($annualProgram->image);
        }
        $annualProgram->delete();
        Cache::forget('annual_programs:all');
        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
