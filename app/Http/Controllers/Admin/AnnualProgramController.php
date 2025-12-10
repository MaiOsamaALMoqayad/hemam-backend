<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\AnnualProgram;
use App\Http\Resources\AnnualProgramResource;
use Intervention\Image\Laravel\Facades\Image;
   use App\Http\Resources\AnnualProgramSummaryResource;

class AnnualProgramController extends Controller
{
    // عرض جميع البرامج (مختصر)

public function index()
{
    try {
        $programs = Cache::remember('annual_programs:all', 3600, function () {
            return AnnualProgram::orderBy('order', 'asc')->get();
        });

        return AnnualProgramSummaryResource::collection($programs);

    } catch (\Throwable $e) {
        Log::error('Annual Programs API Error (index): ' . $e->getMessage());

        return response()->json([
            'status' => false,
            'message' => 'حدث خطأ أثناء جلب البرامج',
        ], 500);
    }
}

    // عرض برنامج واحد (كامل التفاصيل)
    public function show($id)
    {
        try {
            $program = Cache::remember("annual_programs:{$id}", 3600, function () use ($id) {
                return AnnualProgram::findOrFail($id);
            });

            return new AnnualProgramResource($program);

        } catch (\Throwable $e) {
            Log::error("Annual Program API Error (show): " . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'البرنامج المطلوب غير موجود',
            ], 404);
        }
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
        'is_open' => 'nullable|boolean',
        'application_deadline' => 'nullable|string',
        'duration' => 'nullable|string',
        'capacity' => 'nullable|string',
        'history' => 'nullable|string',
    ]);
$data['history'] = isset($data['history']) ? json_decode($data['history'], true) : [];

    // معالجة الصورة
    $imagePath = null;
    if ($request->hasFile('image')) {
        $image = Image::read($request->file('image'));
        $image->cover(800, 600);

        $filename = uniqid() . '.jpg';
        $image->save(storage_path('app/public/annual_programs/' . $filename), 85);

        $imagePath = 'annual_programs/' . $filename;
    }

    $program = AnnualProgram::create([
        'title' => [
            'ar' => $data['title_ar'],
            'en' => $data['title_en'] ?? '',
        ],
        'description' => [
            'ar' => $data['description_ar'],
            'en' => $data['description_en'] ?? '',
        ],
        'image' => $imagePath,
        'order' => $data['order'] ?? 0,
        'is_open' => $data['is_open'] ?? true,
        'application_deadline' => $data['application_deadline'] ?? '',
        'duration' => $data['duration'] ?? '',
        'capacity' => $data['capacity'] ?? '',
    ]);

    // إضافة الـ History
    if (isset($data['history'])) {
        foreach ($data['history'] as $h) {
            $program->histories()->create([
                   'annual_program_id' => $program->id,
                'year' => $h['year'] ?? null,
                'image' => $h['image'] ?? null,
                'achievements' => $h['achievements'] ?? [],
            ]);
        }
    }

    return new AnnualProgramResource($program);
}



public function update(Request $request, AnnualProgram $annualProgram)
{
    try {
        // Validation
        $data = $request->validate([
            'title_ar' => 'sometimes|string|max:255',
            'title_en' => 'sometimes|nullable|string|max:255',
            'description_ar' => 'sometimes|string',
            'description_en' => 'sometimes|nullable|string',
            'image' => 'sometimes|nullable|image|max:2048',
            'order' => 'sometimes|integer',
            'is_open' => 'sometimes|boolean',
            'application_deadline' => 'sometimes|string',
            'duration' => 'sometimes|string',
            'capacity' => 'sometimes|string',
            'history' => 'sometimes|string', // JSON string
        ]);

        // --- تحديث العنوان ---
        $title = $annualProgram->title;
        if ($request->has('title_ar')) {
            $title['ar'] = $data['title_ar'];
        }
        if ($request->has('title_en')) {
            $title['en'] = $data['title_en'] ?? '';
        }

        // --- تحديث الوصف ---
        $description = $annualProgram->description;
        if ($request->has('description_ar')) {
            $description['ar'] = $data['description_ar'];
        }
        if ($request->has('description_en')) {
            $description['en'] = $data['description_en'] ?? '';
        }

        // --- تحديث الصورة ---
        $imagePath = $annualProgram->image;
        if ($request->hasFile('image')) {

            // حذف الصورة القديمة
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            $image = Image::read($request->file('image'));
            $image->cover(800, 600);

            $filename = uniqid() . '.jpg';
            $image->save(storage_path('app/public/annual_programs/' . $filename), 85);

            $imagePath = 'annual_programs/' . $filename;
        }

        // --- تحديث البرنامج الرئيسي ---
        $annualProgram->update([
            'title' => $title,
            'description' => $description,
            'image' => $imagePath,
            'order' => $data['order'] ?? $annualProgram->order,
            'is_open' => $data['is_open'] ?? $annualProgram->is_open,
            'application_deadline' => $data['application_deadline'] ?? $annualProgram->application_deadline,
            'duration' => $data['duration'] ?? $annualProgram->duration,
            'capacity' => $data['capacity'] ?? $annualProgram->capacity,
        ]);

        // --- تحديث الـ HISTORY ---
        if ($request->has('history')) {

            $historyData = json_decode($data['history'], true);

            // حذف القديم
            $annualProgram->histories()->delete();

            // إضافة الجديد
            foreach ($historyData as $h) {
                $annualProgram->histories()->create([
                    'annual_program_id' => $annualProgram->id,
                    'year' => $h['year'] ?? null,
                    'image' => $h['image'] ?? null,
                    'achievements' => $h['achievements'] ?? [],
                ]);
            }
        }

        // Clear Cache
        Cache::forget('annual_programs:all');
        Cache::forget("annual_programs:{$annualProgram->id}");

        return new AnnualProgramResource($annualProgram);

    } catch (\Throwable $e) {
        Log::error("Annual Programs API Error (update): " . $e->getMessage());

        return response()->json([
            'status' => false,
            'message' => 'حدث خطأ أثناء تحديث البرنامج',
            'error' => $e->getMessage(),
        ], 500);
    }
}






    // حذف برنامج
    public function destroy(AnnualProgram $annualProgram)
    {
        if ($annualProgram->image) {
            Storage::disk('public')->delete($annualProgram->image);
        }

        $annualProgram->delete();
        Cache::forget('annual_programs:all');

        return response()->json(['message' => 'تم حذف البرنامج بنجاح']);
    }
}
