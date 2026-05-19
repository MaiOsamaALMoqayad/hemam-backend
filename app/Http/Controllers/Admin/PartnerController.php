<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePartnerRequest;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class PartnerController extends Controller
{

    public function index()
    {
        $partners = Partner::orderBy('order_index', 'asc')->get();
        return response()->json(PartnerResource::collection($partners));
    }

    public function store(StorePartnerRequest $request)
    {
        $validated = $request->validated();

        $image = Image::read($request->file('image'));

        $image->cover(400, 400);

        $filename = uniqid() . '.jpg';

        if (!Storage::disk('public')->exists('partners')) {
            Storage::disk('public')->makeDirectory('partners');
        }

        $image->save(storage_path('app/public/partners/' . $filename), quality: 85);

        $partner = Partner::create([
            'name'        => $validated['name'] ?? null,
            'image'       => 'partners/' . $filename,
            'order_index' => $validated['order_index'] ?? 0,
        ]);
 
        return response()->json([
            'success' => true,
            'message' => 'تم إضافة الصورة بنجاح.',
            'data'    => new PartnerResource($partner)
        ], 201);
    }


    public function destroy($id)
    {
        $partner = Partner::find($id);

        if (!$partner) {
            return response()->json([
                'success' => false,
                'message' => 'الشريك غير موجود.'
            ], 404);
        }

        if ($partner->image && Storage::disk('public')->exists($partner->image)) {
            Storage::disk('public')->delete($partner->image);
        }

        $partner->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الشريك وصورته بنجاح.'
        ], 200);
    }
}
