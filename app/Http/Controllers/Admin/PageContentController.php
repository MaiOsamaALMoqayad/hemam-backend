<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PageContentController extends Controller
{
    /**
     * PUT /admin/content — save several values at once.
     *
     * Body: {"contents": [{"key": "home.hero.title", "value": "..."}]}
     * An empty value deletes the row, so the site falls back to its default.
     */
    public function update(Request $request)
    {
        $keys = config('page_content.keys', []);

        $request->validate([
            'contents'         => 'required|array|min:1|max:200',
            'contents.*.key'   => ['required', 'string', 'distinct', Rule::in(array_keys($keys))],
            'contents.*.value' => 'present|nullable|string',
        ], [
            'contents.*.key.in' => 'حقل غير قابل للتعديل.',
        ]);

        $entries = [];
        foreach ($request->input('contents') as $i => $entry) {
            $key = $entry['key'];
            $value = $this->cleanText($entry['value'] ?? '');
            $max = $keys[$key]['max'] ?? 1000;

            if (mb_strlen($value) > $max) {
                throw ValidationException::withMessages([
                    "contents.$i.value" => ["النص أطول من الحد المسموح ($max حرفاً)."],
                ]);
            }

            $entries[$key] = $value;
        }

        DB::transaction(function () use ($entries, $keys, $request) {
            foreach ($entries as $key => $value) {
                if ($value === '') {
                    PageContent::where('key', $key)->delete();
                    continue;
                }

                PageContent::updateOrCreate(
                    ['key' => $key],
                    [
                        'type'       => $keys[$key]['type'] ?? 'text',
                        'value'      => $value,
                        'updated_by' => $request->user()?->id,
                    ]
                );
            }
        });

        PageContent::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ التعديلات بنجاح.',
            'data'    => (object) PageContent::allValues(),
        ]);
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
