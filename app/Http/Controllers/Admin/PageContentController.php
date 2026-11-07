<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Laravel\Facades\Image;

class PageContentController extends Controller
{
    /**
     * PUT /admin/content — save several values at once.
     *
     * Body: {"contents": [{"key": "...", "value": ...}]}
     *   text:  value is a string.
     *   link:  value is {"label": "...", "url": "..."}.
     *   image: only "" is accepted here (restore the default image);
     *          uploading goes through uploadImage().
     * An empty value ("" or a link with nothing in it) deletes the row, so
     * the site falls back to its built-in default.
     */
    public function update(Request $request)
    {
        $keys = config('page_content.keys', []);

        $request->validate([
            'contents'       => 'required|array|min:1|max:200',
            'contents.*.key' => ['required', 'string', 'distinct', Rule::in(array_keys($keys))],
            'contents.*'     => 'array:key,value',
        ], [
            'contents.*.key.in' => 'حقل غير قابل للتعديل.',
        ]);

        $entries = [];
        foreach ($request->input('contents') as $i => $entry) {
            $key = $entry['key'];
            $entries[$key] = $this->cleanValue($keys[$key], $entry['value'] ?? null, "contents.$i.value");
        }

        $replacedImages = [];

        DB::transaction(function () use ($entries, $keys, $request, &$replacedImages) {
            foreach ($entries as $key => $value) {
                $existing = PageContent::where('key', $key)->first();

                if ($value === null) {
                    if ($existing?->type === 'image') {
                        $replacedImages[] = $existing->value;
                    }
                    $existing?->delete();
                    continue;
                }

                PageContent::updateOrCreate(
                    ['key' => $key],
                    [
                        'type'       => $keys[$key]['type'],
                        'value'      => $value,
                        'updated_by' => $request->user()?->id,
                    ]
                );
            }
        });

        $this->deleteImageFiles($replacedImages);
        PageContent::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ التعديلات بنجاح.',
            'data'    => (object) PageContent::publicValues(),
        ]);
    }

    /**
     * POST /admin/content/image — multipart {key, image}. Scales the image
     * down to the key's configured width, keeps PNG/WebP as is (logos need
     * their transparency) and stores everything else as JPEG. Saved
     * immediately; the previous upload for that key is deleted.
     */
    public function uploadImage(Request $request)
    {
        $imageKeys = collect(config('page_content.keys', []))
            ->filter(fn ($def) => $def['type'] === 'image')
            ->keys()
            ->all();

        $request->validate([
            'key'   => ['required', 'string', Rule::in($imageKeys)],
            'image' => 'required|file|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'key.in'       => 'هذه الصورة غير قابلة للتعديل.',
            'image.mimes'  => 'الصيغ المسموحة: JPG أو PNG أو WebP.',
            'image.max'    => 'حجم الصورة يجب ألا يتجاوز 5 ميغابايت.',
        ]);

        $key = $request->input('key');
        $file = $request->file('image');
        $width = PageContent::definition($key)['width'] ?? 1920;

        $image = Image::read($file)->scaleDown(width: $width);

        $extension = strtolower($file->getClientOriginalExtension());
        [$encoded, $extension] = match ($extension) {
            'png'   => [$image->toPng(), 'png'],
            'webp'  => [$image->toWebp(quality: 85), 'webp'],
            default => [$image->toJpeg(quality: 85), 'jpg'],
        };

        $path = PageContent::IMAGE_DIR . '/' . uniqid() . '.' . $extension;
        Storage::disk('public')->put($path, (string) $encoded);

        $previous = PageContent::where('key', $key)->value('value');

        PageContent::updateOrCreate(
            ['key' => $key],
            ['type' => 'image', 'value' => $path, 'updated_by' => $request->user()?->id]
        );

        if ($previous) {
            $this->deleteImageFiles([$previous]);
        }
        PageContent::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ الصورة بنجاح.',
            'data'    => ['key' => $key, 'url' => asset('storage/' . $path)],
        ]);
    }

    /**
     * Validates and normalizes one value for its key type. Returns null for
     * "empty", meaning restore the default.
     */
    private function cleanValue(array $def, mixed $value, string $field): ?string
    {
        switch ($def['type']) {
            case 'image':
                if ($value !== null && $value !== '') {
                    $this->fail($field, 'تُرفع الصور من نافذة تغيير الصورة.');
                }
                return null;

            case 'link':
                if ($value === null || $value === '') {
                    return null;
                }
                if (!is_array($value)) {
                    $this->fail($field, 'قيمة الرابط غير صحيحة.');
                }

                $hasLabel = ($def['label'] ?? true) !== false;
                $label = $hasLabel ? $this->cleanText((string) ($value['label'] ?? '')) : '';
                $url = trim((string) ($value['url'] ?? ''));

                if ($label === '' && $url === '') {
                    return null;
                }
                if ($hasLabel && $label === '') {
                    $this->fail($field, 'نص الرابط مطلوب.');
                }
                $max = $def['max'] ?? 60;
                if (mb_strlen($label) > $max) {
                    $this->fail($field, "نص الرابط أطول من الحد المسموح ($max حرفاً).");
                }
                if (!$this->isAllowedUrl($url)) {
                    $this->fail($field, 'الرابط غير صحيح. استخدم رابطاً يبدأ بـ https:// أو مساراً داخلياً مثل /programs.');
                }

                return json_encode(['label' => $label, 'url' => $url], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            default: // text
                if ($value !== null && !is_string($value)) {
                    $this->fail($field, 'قيمة النص غير صحيحة.');
                }
                $text = $this->cleanText($value ?? '');
                $max = $def['max'] ?? 1000;
                if (mb_strlen($text) > $max) {
                    $this->fail($field, "النص أطول من الحد المسموح ($max حرفاً).");
                }

                return $text === '' ? null : $text;
        }
    }

    /**
     * Internal paths (/programs, #section), http(s), mailto: and tel: only,
     * so a saved link can never be javascript: or a protocol-relative URL.
     */
    private function isAllowedUrl(string $url): bool
    {
        if ($url === '' || mb_strlen($url) > 500 || preg_match('/\s/u', $url)) {
            return false;
        }
        if (str_starts_with($url, '#') || (str_starts_with($url, '/') && !str_starts_with($url, '//'))) {
            return true;
        }
        if (preg_match('/^(mailto|tel):.+/i', $url)) {
            return true;
        }

        return preg_match('#^https?://#i', $url) === 1 && filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    private function deleteImageFiles(array $paths): void
    {
        foreach ($paths as $path) {
            // Only ever delete files this feature uploaded.
            if (is_string($path) && str_starts_with($path, PageContent::IMAGE_DIR . '/')) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => [$message]]);
    }

    /**
     * Plain text only: strip tags, normalize line breaks, collapse runs of
     * blank lines, trim.
     */
    private function cleanText(string $value): string
    {
        $value = strip_tags($value);
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = preg_replace('/[ \t]+\n/u', "\n", $value);
        $value = preg_replace("/\n{3,}/u", "\n\n", $value);

        return trim($value);
    }
}
