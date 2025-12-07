<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Cache;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class SettingController extends Controller
{
    public function index()
    {
        return response()->json(Setting::all());
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value_ar' => 'required|string',
            'settings.*.value_en' => 'nullable|string',
        ]);

        foreach ($data['settings'] as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => [
                        'ar' => $setting['value_ar'],
                        'en' => $setting['value_en'] ?? ''
                    ]
                ]
            );
        }

        Cache::forget('settings:all');
        Setting::clearCache();

        return response()->json(['message' => 'تم التحديث بنجاح']);
    }
}
