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
            // New partners go last unless an order is given.
            'order_index' => $validated['order_index'] ?? ((int) Partner::max('order_index') + 1),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'تم إضافة الصورة بنجاح.',
            'data'    => new PartnerResource($partner)
        ], 201);
    }


    /**
     * POST /admin/partners/{id} — multipart {name?, image?}. POST rather
     * than PUT because PHP doesn't parse multipart bodies on PUT.
     */
    public function update(Request $request, $id)
    {
        $partner = Partner::find($id);

        if (!$partner) {
            return response()->json([
                'success' => false,
                'message' => 'الشريك غير موجود.'
            ], 404);
        }

        $validated = $request->validate([
            'name'  => 'nullable|string|max:150',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = ['name' => $validated['name'] ?? null];

        if ($request->hasFile('image')) {
            $image = Image::read($request->file('image'))->cover(400, 400);
            $path = 'partners/' . uniqid() . '.jpg';
            Storage::disk('public')->put($path, (string) $image->toJpeg(quality: 85));

            if ($partner->image && Storage::disk('public')->exists($partner->image)) {
                Storage::disk('public')->delete($partner->image);
            }
            $data['image'] = $path;
        }

        $partner->update($data);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الشريك بنجاح.',
            'data'    => new PartnerResource($partner)
        ], 200);
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
