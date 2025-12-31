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
use App\Models\HistoryImage;

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
            'history' => 'nullable|array',
        ]);
        $data['history'] = $data['history'] ?? [];

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
        if ($request->has('history')) {
            $this->processHistory($program, $request->input('history'), $request);
        }
        Cache::forget('annual_programs:all');

        return response()->json([
            'data' => new AnnualProgramResource($program)
        ]);
    }


    public function update(Request $request, AnnualProgram $annualProgram)
    {
        try {
            $data = $request->validate([
                'title_ar' => 'sometimes|string|max:255',
                'title_en' => 'sometimes|nullable|string|max:255',
                'description_ar' => 'sometimes|string',
                'description_en' => 'sometimes|nullable|string',
                'image' => 'sometimes|nullable|image|max:2048',
                'order' => 'sometimes|integer',
                'is_open' => 'sometimes',
                'application_deadline' => 'sometimes|string',
                'duration' => 'sometimes|string',
                'capacity' => 'sometimes|string',
                'history' => 'sometimes|array',
            ]);

            // تحديث العنوان والوصف
            // --- تحديث العنوان ---
            $title = $annualProgram->title;

            // تأكد أن $title مصفوفة، إذا كان نصاً قم بتحويله
            if (is_string($title)) {
                $title = json_decode($title, true) ?? [];
            }

            if ($request->has('title_ar')) {
                $title['ar'] = $data['title_ar'];
            }
            if ($request->has('title_en')) {
                $title['en'] = $data['title_en'] ?? '';
            }

            // --- تحديث الوصف ---
            $description = $annualProgram->description;

            // تأكد أن $description مصفوفة
            if (is_string($description)) {
                $description = json_decode($description, true) ?? [];
            }

            if ($request->has('description_ar')) {
                $description['ar'] = $data['description_ar'];
            }
            if ($request->has('description_en')) {
                $description['en'] = $data['description_en'] ?? '';
            }

            // تحديث الصورة الرئيسية
            $imagePath = $annualProgram->image;
            if ($request->hasFile('image')) {
                if ($imagePath) Storage::disk('public')->delete($imagePath);
                $image = Image::read($request->file('image'))->cover(800, 600);
                $filename = uniqid() . '.jpg';
                $image->save(storage_path('app/public/annual_programs/' . $filename), 85);
                $imagePath = 'annual_programs/' . $filename;
            }

            $annualProgram->update([
                'title' => $title,
                'description' => $description,
                'image' => $imagePath,
                'order' => $data['order'] ?? $annualProgram->order,
                'is_open' => $request->has('is_open') ? filter_var($data['is_open'], FILTER_VALIDATE_BOOLEAN) : $annualProgram->is_open,
                'application_deadline' => $data['application_deadline'] ?? $annualProgram->application_deadline,
                'duration' => $data['duration'] ?? $annualProgram->duration,
                'capacity' => $data['capacity'] ?? $annualProgram->capacity,
            ]);

            // تحديث الـ History بشكل ذكي
            if ($request->has('history')) {
                $this->processHistory($annualProgram, $request->input('history'), $request);
            }

            Cache::forget('annual_programs:all');
            Cache::forget("annual_programs:{$annualProgram->id}");

            return new AnnualProgramResource($annualProgram);
        } catch (\Throwable $e) {
            Log::error("Annual Programs Update Error: " . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'حدث خطأ أثناء التحديث', 'error' => $e->getMessage()], 500);
        }
    }






    // حذف برنامج
    public function destroy(AnnualProgram $annualProgram)
    {
        if ($annualProgram->image) {
            Storage::disk('public')->delete($annualProgram->image);
        }

        // حذف صور التاريخ ايضاً
        foreach ($annualProgram->histories as $history) {
            foreach ($history->images as $img) {
                Storage::disk('public')->delete($img->image);
            }
        }

        $annualProgram->delete();
        Cache::forget('annual_programs:all');

        return response()->json(['message' => 'تم حذف البرنامج بنجاح']);
    }

 private function processHistory($program, $historyData, $request)
{
    foreach ($historyData as $index => $h) {

        // 1. إيجاد أو إنشاء سنة
        $history = $program->histories()
            ->firstOrCreate(
                ['year' => $h['year']],
                ['achievements' => $h['achievements'] ?? []]
            );

        // 2. تحديث الإنجازات
        if (isset($h['achievements'])) {
            $history->update([
                'achievements' => $h['achievements']
            ]);
        }

        // 3. إضافة صور متعددة
        if ($request->hasFile("history.$index.images")) {

            foreach ($request->file("history.$index.images") as $file) {

                $image = Image::read($file)->cover(400, 300);
                $filename = uniqid() . '_h.jpg';

                $image->save(
                    storage_path("app/public/annual_programs/$filename"),
                    85
                );

                $history->images()->create([
                    'image' => "annual_programs/$filename"
                ]);
            }
        }
    }
}

}
