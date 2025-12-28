<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Project, ProjectImage};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Storage, DB, Cache, Log};
use Intervention\Image\Laravel\Facades\Image;

class ProjectController extends Controller
{
    /**
     * عرض كل المشاريع مع الصور
     */
 public function index()
{

    $projects = Cache::remember('projects:all', 60, function () {
        return Project::with('images')
            ->orderBy('order')
            ->get()
            ->map(function ($project) {

                $project->images->transform(function ($img) {
                    if ($img->image) {
                        $img->image = asset('storage/' . $img->image);
                    }
                    return $img;
                });

                return $project;
            });
    });

    return response()->json($projects);
}


    /**
     * إنشاء مشروع جديد مع رفع الصور
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title_ar' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'description_ar' => 'required|string',
            'description_en' => 'nullable|string',
            'images' => 'required|array|min:1',
            'images.*' => 'image|max:2048',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $project = Project::create([
                'title' => ['ar' => $data['title_ar'], 'en' => $data['title_en'] ?? ''],
                'description' => ['ar' => $data['description_ar'], 'en' => $data['description_en'] ?? ''],
                'order' => $data['order'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
            ]);

            foreach ($request->file('images') as $index => $file) {
                $filename = $this->saveImage($file, 'projects');
                ProjectImage::create([
                    'project_id' => $project->id,
                    'image' => 'projects/' . $filename,
                    'order' => $index,
                ]);
            }

            DB::commit();
            Cache::forget('projects:all');

            $project->load('images');

            // 2. تعديل مسارات الصور باستخدام transform
            $project->images->transform(function ($img) {
                $img->image = $img->image ? asset('storage/' . $img->image) : null;
                return $img;
            });

            // 3. إرجاع كائن المشروع المفرد
            return response()->json($project, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Project store error: ' . $e->getMessage());
            return response()->json(['message' => 'حدث خطأ أثناء إنشاء المشروع'], 500);
        }
    }

    /**
     * عرض مشروع محدد
     */
    public function show(Project $project)
    {
        $project->load('images');
        $project->images->transform(fn($img) => $img->image = $img->image ? asset('storage/' . $img->image) : null);
        return response()->json($project);
    }

    /**
     * تحديث مشروع
     */
    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'title_ar' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'description_ar' => 'required|string',
            'description_en' => 'nullable|string',
            'images.*' => 'mimes:jpeg,png,jpg|max:2048',
            'images.*' => 'mimes:jpeg,png,jpg|max:2048',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $project->update([
                'title' => ['ar' => $data['title_ar'], 'en' => $data['title_en'] ?? ''],
                'description' => ['ar' => $data['description_ar'], 'en' => $data['description_en'] ?? ''],
                'order' => $data['order'] ?? $project->order,
                'is_active' => $data['is_active'] ?? $project->is_active,
            ]);

            if ($request->hasFile('images')) {
                // حذف الصور القديمة
                foreach ($project->images as $img) {
                    Storage::disk('public')->delete($img->image);
                }
                $project->images()->delete();

                // رفع الصور الجديدة
                foreach ($request->file('images') as $index => $file) {
                    $filename = $this->saveImage($file, 'projects');
                    ProjectImage::create([
                        'project_id' => $project->id,
                        'image' => 'projects/' . $filename,
                        'order' => $index,
                    ]);
                }
            }

            DB::commit();
            Cache::forget('projects:all');

            $project->load('images');
            $project->images->transform(fn($img) => $img->image = $img->image ? asset('storage/' . $img->image) : null);
            return response()->json($project);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Project update error: ' . $e->getMessage());
            return response()->json(['message' => 'حدث خطأ أثناء تحديث المشروع'], 500);
        }
    }

    /**
     * حذف مشروع
     */
    public function destroy(Project $project)
    {
        foreach ($project->images as $img) {
            Storage::disk('public')->delete($img->image);
        }
        $project->delete();
        Cache::forget('projects:all');
        return response()->json(['message' => 'تم الحذف بنجاح']);
    }

    /**
     * حفظ الصورة وتعديل حجمها
     */
    private function saveImage($file, $folder)
    {
        $image = Image::read($file);
        $image->cover(800, 600);
        $filename = uniqid() . '.jpg';
        $image->save(storage_path("app/public/$folder/$filename"), 85);
        return $filename;
    }
}
