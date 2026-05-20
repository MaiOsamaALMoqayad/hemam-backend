<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Statistics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StatisticsController extends Controller
{
    private function getIconsList()
    {
        return [
            'Users',
            'Building2',
            'GraduationCap',
            'MessageSquare',
            'Briefcase',
            'Trophy',
            'Heart',
            'Calendar',
            'Lightbulb',
            'Target',
            'Award',
            'BookOpen'
        ];
    }

    /**
     * 2. مسار إرسال الأيقونات المتاحة للفرونت إند (ليعرضها في الـ Dropdown)
     */
    public function getAvailableIcons()
    {
        return response()->json([
            'success' => true,
            'icons'   => $this->getIconsList()
        ], 200);
    }

    /**
     * 3. عرض جميع الإحصائيات الحالية في لوحة التحكم
     */
    public function index()
    {
        $statistics = Statistics::all();

        return response()->json([
            'success' => true,
            'data'    => $statistics
        ], 200);
    }

    /**
     * 4. إضافة إحصائية جديدة (بأي عنوان حر، وبشرط عدم تجاوز 4 إحصائيات)
     */
    public function store(Request $request)
    {
        if (Statistics::count() >= 4) {
            return response()->json([
                'success' => false,
                'message' => 'عذراً، يجب أن يحتوي الموقع على 4 إحصائيات فقط. يمكنك تعديل الإحصائيات الحالية أو حذف إحداها لإضافة جديدة.'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'title'     => 'required|string|max:255',
            'count'     => 'required|string|max:50',
            'icon_name' => [
                'required',
                'string',
                Rule::in($this->getIconsList())
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $statistic = Statistics::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة الإحصائية بنجاح.',
            'data'    => $statistic
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $statistic = Statistics::find($id);

        if (!$statistic) {
            return response()->json([
                'success' => false,
                'message' => 'الإحصائية غير موجودة.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title'     => 'required|string|max:255',
            'count'     => 'required|string|max:50',
            'icon_name' => [
                'required',
                'string',
                Rule::in($this->getIconsList())
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $statistic->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الإحصائية بنجاح.',
            'data'    => $statistic
        ], 200);
    }

  
    public function destroy($id)
    {
        $statistic = Statistics::find($id);

        if (!$statistic) {
            return response()->json([
                'success' => false,
                'message' => 'الإحصائية غير موجودة.'
            ], 404);
        }

        $statistic->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الإحصائية بنجاح.'
        ], 200);
    }
}
