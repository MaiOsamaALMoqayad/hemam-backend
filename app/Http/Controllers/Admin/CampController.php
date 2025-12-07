<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\{Storage, DB, Log};
use App\Models\{Camp, CampLocation, CampLearning, CampActivity, CampImage};

class CampController extends Controller
{
    public function index()
    {
        try {
            $camps = Camp::with(['locations', 'learnings', 'activities', 'images'])
                ->orderBy('start_date', 'desc')
                ->get()
                ->map(function ($camp) {
                    return $this->formatCampResponse($camp);
                });

            return response()->json($camps);
        } catch (\Exception $e) {
            Log::error('Admin Camps Index Error: ' . $e->getMessage());
            return response()->json(['message' => 'حدث خطأ أثناء جلب المخيمات'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'title_ar' => 'required|string|max:255',
                'title_en' => 'nullable|string|max:255',
                'description_ar' => 'required|string|max:5000',
                'description_en' => 'nullable|string',
                'about_ar' => 'nullable|string',
                'about_en' => 'nullable|string',
                'main_image' => 'required|image|max:2048',
                'age_range' => 'required|string|max:20',
                'start_date' => 'required|date',
                'duration' => 'required|integer|min:1',
                'capacity' => 'required|integer|min:1',
                'is_open' => 'required|boolean',
                'status' => 'required|in:upcoming,ongoing,completed',
                'locations' => 'required|array|min:1',
                'locations.*.name_ar' => 'required|string',
                'locations.*.name_en' => 'nullable|string',
                'learnings' => 'nullable|array',
                'learnings.*.title_ar' => 'required|string',
                'learnings.*.title_en' => 'nullable|string',
                'activities' => 'nullable|array',
                'activities.*.title_ar' => 'required|string',
                'activities.*.title_en' => 'nullable|string',
                'activities.*.description_ar' => 'required|string',
                'activities.*.description_en' => 'nullable|string',
                'images' => 'nullable|array',
                'images.*' => 'image|max:2048',
            ]);

            DB::beginTransaction();

            // رفع الصورة الرئيسية
            $mainImagePath = $this->uploadImage($request->file('main_image'), 'camps', 1200, 800);
            Log::info('Camp main image uploaded: ' . $mainImagePath);

            // إنشاء المخيم
            $camp = Camp::create([
                'title' => ['ar' => $data['title_ar'], 'en' => $data['title_en'] ?? ''],
                'description' => ['ar' => $data['description_ar'], 'en' => $data['description_en'] ?? ''],
                'about' => ['ar' => $data['about_ar'] ?? '', 'en' => $data['about_en'] ?? ''],
                'main_image' => $mainImagePath,
                'age_range' => $data['age_range'],
                'start_date' => $data['start_date'],
                'duration' => $data['duration'],
                'capacity' => $data['capacity'],
                'is_open' => $data['is_open'],
                'status' => $data['status'],
            ]);

            Log::info('Camp created: ' . $camp->id);

            // إضافة المواقع
            foreach ($data['locations'] as $index => $loc) {
                CampLocation::create([
                    'camp_id' => $camp->id,
                    'name' => ['ar' => $loc['name_ar'], 'en' => $loc['name_en'] ?? ''],
                    'order' => $index,
                ]);
            }
            Log::info('Camp locations added: ' . count($data['locations']));

            // إضافة ماذا ستتعلم
            if (!empty($data['learnings'])) {
                foreach ($data['learnings'] as $index => $learning) {
                    CampLearning::create([
                        'camp_id' => $camp->id,
                        'title' => ['ar' => $learning['title_ar'], 'en' => $learning['title_en'] ?? ''],
                        'order' => $index,
                    ]);
                }
                Log::info('Camp learnings added: ' . count($data['learnings']));
            }

            // إضافة الأنشطة
            if (!empty($data['activities'])) {
                foreach ($data['activities'] as $index => $activity) {
                    CampActivity::create([
                        'camp_id' => $camp->id,
                        'title' => ['ar' => $activity['title_ar'], 'en' => $activity['title_en'] ?? ''],
                        'description' => ['ar' => $activity['description_ar'], 'en' => $activity['description_en'] ?? ''],
                        'order' => $index,
                    ]);
                }
                Log::info('Camp activities added: ' . count($data['activities']));
            }

            // إضافة معرض الصور
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $imagePath = $this->uploadImage($file, 'camps', 800, 600);

                    CampImage::create([
                        'camp_id' => $camp->id,
                        'image' => $imagePath,
                        'order' => $index,
                    ]);
                }
                Log::info('Camp gallery images added: ' . count($request->file('images')));
            }

            DB::commit();

            // مسح الـ Cache
            Cache::forget('camps:open');
            Cache::forget('camps:closed');

            Log::info('Camp created successfully: ' . $camp->id);

            return response()->json($this->formatCampResponse($camp->load(['locations', 'learnings', 'activities', 'images'])), 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::warning('Camp validation failed: ' . json_encode($e->errors()));
            return response()->json(['message' => 'خطأ في البيانات المدخلة', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Camp Store Error: ' . $e->getMessage() . ' | Line: ' . $e->getLine());
            return response()->json(['message' => 'حدث خطأ أثناء إنشاء المخيم: ' . $e->getMessage()], 500);
        }
    }

    public function show(Camp $camp)
    {
        try {
            $camp->load(['locations', 'learnings', 'activities', 'images']);
            return response()->json($this->formatCampResponse($camp));
        } catch (\Exception $e) {
            Log::error('Camp Show Error: ' . $e->getMessage());
            return response()->json(['message' => 'حدث خطأ أثناء جلب تفاصيل المخيم'], 500);
        }
    }

 public function update(Request $request, Camp $camp)
{
    try {
        // تحقق من صحة البيانات إذا أرسلت
        $data = $request->validate([
            'title_ar' => 'nullable|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'about_ar' => 'nullable|string',
            'about_en' => 'nullable|string',
            'main_image' => 'nullable|image|max:2048',
            'age_range' => 'nullable|string|max:20',
            'start_date' => 'nullable|date',
            'duration' => 'nullable|integer|min:1',
            'capacity' => 'nullable|integer|min:1',
            'is_open' => 'nullable|boolean',
            'status' => 'nullable|in:upcoming,ongoing,completed',
            'locations' => 'nullable|array|min:1',
            'locations.*.name_ar' => 'required_with:locations|string',
            'locations.*.name_en' => 'nullable|string',
            'learnings' => 'nullable|array',
            'learnings.*.title_ar' => 'required_with:learnings|string',
            'learnings.*.title_en' => 'nullable|string',
            'activities' => 'nullable|array',
            'activities.*.title_ar' => 'required_with:activities|string',
            'activities.*.title_en' => 'nullable|string',
            'activities.*.description_ar' => 'required_with:activities|string',
            'activities.*.description_en' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|max:2048',
        ]);

        DB::beginTransaction();

        // تحديث الصورة الرئيسية إذا تم رفع واحدة جديدة
        if ($request->hasFile('main_image')) {
            Storage::disk('public')->delete($camp->main_image);
            $mainImagePath = $this->uploadImage($request->file('main_image'), 'camps', 1200, 800);
            $camp->main_image = $mainImagePath;
        }

        // تحديث بيانات المخيم جزئيًا
        $camp->update([
            'title' => [
                'ar' => $request->has('title_ar') ? $data['title_ar'] : $camp->title['ar'],
                'en' => $request->has('title_en') ? $data['title_en'] : $camp->title['en'],
            ],
            'description' => [
                'ar' => $request->has('description_ar') ? $data['description_ar'] : $camp->description['ar'],
                'en' => $request->has('description_en') ? $data['description_en'] : $camp->description['en'],
            ],
            'about' => [
                'ar' => $request->has('about_ar') ? $data['about_ar'] : $camp->about['ar'],
                'en' => $request->has('about_en') ? $data['about_en'] : $camp->about['en'],
            ],
            'age_range' => $request->has('age_range') ? $data['age_range'] : $camp->age_range,
            'start_date' => $request->has('start_date') ? $data['start_date'] : $camp->start_date,
            'duration' => $request->has('duration') ? $data['duration'] : $camp->duration,
            'capacity' => $request->has('capacity') ? $data['capacity'] : $camp->capacity,
            'is_open' => $request->has('is_open') ? $data['is_open'] : $camp->is_open,
            'status' => $request->has('status') ? $data['status'] : $camp->status,
        ]);

        // تحديث المواقع إذا تم إرسالها
        if (isset($data['locations'])) {
            $camp->locations()->delete();
            foreach ($data['locations'] as $index => $loc) {
                CampLocation::create([
                    'camp_id' => $camp->id,
                    'name' => ['ar' => $loc['name_ar'], 'en' => $loc['name_en'] ?? ''],
                    'order' => $index,
                ]);
            }
        }

        // تحديث Learnings
        if (isset($data['learnings'])) {
            $camp->learnings()->delete();
            foreach ($data['learnings'] as $index => $learning) {
                CampLearning::create([
                    'camp_id' => $camp->id,
                    'title' => ['ar' => $learning['title_ar'], 'en' => $learning['title_en'] ?? ''],
                    'order' => $index,
                ]);
            }
        }

        // تحديث Activities
        if (isset($data['activities'])) {
            $camp->activities()->delete();
            foreach ($data['activities'] as $index => $activity) {
                CampActivity::create([
                    'camp_id' => $camp->id,
                    'title' => ['ar' => $activity['title_ar'], 'en' => $activity['title_en'] ?? ''],
                    'description' => ['ar' => $activity['description_ar'], 'en' => $activity['description_en'] ?? ''],
                    'order' => $index,
                ]);
            }
        }

        // تحديث الصور إذا تم إرسالها
        if ($request->hasFile('images')) {
            foreach ($camp->images as $img) {
                Storage::disk('public')->delete($img->image);
            }
            $camp->images()->delete();
            foreach ($request->file('images') as $index => $file) {
                $imagePath = $this->uploadImage($file, 'camps', 800, 600);
                CampImage::create([
                    'camp_id' => $camp->id,
                    'image' => $imagePath,
                    'order' => $index,
                ]);
            }
        }

        DB::commit();

        // مسح الـ Cache
        Cache::forget('camps:open');
        Cache::forget('camps:closed');
        Cache::forget("camp:details:{$camp->id}");

        return response()->json($this->formatCampResponse($camp->load(['locations', 'learnings', 'activities', 'images'])));
    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return response()->json(['message' => 'خطأ في البيانات المدخلة', 'errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'حدث خطأ أثناء تحديث المخيم: ' . $e->getMessage()], 500);
    }
}


    public function destroy(Camp $camp)
    {
        try {
            DB::beginTransaction();

            // حذف الصورة الرئيسية
            Storage::disk('public')->delete($camp->main_image);

            // حذف معرض الصور
            foreach ($camp->images as $img) {
                Storage::disk('public')->delete($img->image);
            }

            // حذف المخيم (cascade سيحذف كل العلاقات)
            $camp->delete();

            DB::commit();

            // مسح الـ Cache
            Cache::forget('camps:open');
            Cache::forget('camps:closed');
            Cache::forget("camp:details:{$camp->id}");

            Log::info('Camp deleted successfully: ' . $camp->id);

            return response()->json(['message' => 'تم الحذف بنجاح']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Camp Delete Error: ' . $e->getMessage());
            return response()->json(['message' => 'حدث خطأ أثناء حذف المخيم'], 500);
        }
    }

    /**
     * رفع الصورة ومعالجتها
     */
    private function uploadImage($file, $folder, $width, $height)
    {
        $image = Image::read($file);
        $image->cover($width, $height);
        $filename = uniqid() . '.jpg';
        $image->save(storage_path("app/public/{$folder}/" . $filename), quality: 85);

        return "{$folder}/" . $filename;
    }

    /**
     * تنسيق response المخيم مع URLs كاملة
     */
    private function formatCampResponse($camp)
    {
        return [
            'id' => $camp->id,
            'title' => $camp->title,
            'description' => $camp->description,
            'about' => $camp->about,
            'main_image' => $camp->main_image ? asset('storage/' . $camp->main_image) : null,
            'age_range' => $camp->age_range,
            'start_date' => $camp->start_date->format('Y-m-d'),
            'duration' => $camp->duration,
            'capacity' => $camp->capacity,
            'is_open' => $camp->is_open,
            'status' => $camp->status,
            'order' => $camp->order ?? 0,
            'locations' => $camp->locations->map(fn($loc) => [
                'id' => $loc->id,
                'name' => $loc->name,
                'order' => $loc->order,
            ]),
            'learnings' => $camp->learnings->map(fn($learning) => [
                'id' => $learning->id,
                'title' => $learning->title,
                'order' => $learning->order,
            ]),
            'activities' => $camp->activities->map(fn($activity) => [
                'id' => $activity->id,
                'title' => $activity->title,
                'description' => $activity->description,
                'order' => $activity->order,
            ]),
            'images' => $camp->images->map(fn($img) => [
                'id' => $img->id,
                'url' => asset('storage/' . $img->image),
                'order' => $img->order,
            ]),
            'created_at' => $camp->created_at,
            'updated_at' => $camp->updated_at,
        ];
    }
}
