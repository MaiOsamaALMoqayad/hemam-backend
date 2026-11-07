<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\Partner;
use App\Models\Statistics;
use App\Models\Trainer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReorderController extends Controller
{
    /** resource => [model, order column, cache keys to forget] */
    private const RESOURCES = [
        'goals'      => [Goal::class, 'order_index', []], // the about page's goals
        'statistics' => [Statistics::class, 'order_index', []],
        'partners'   => [Partner::class, 'order_index', []],
        'trainers'   => [Trainer::class, 'order', ['trainers:all']],
    ];

    /**
     * POST /admin/reorder/{resource} — {"ids": [3, 1, 2]}.
     * Every id of the resource must be listed exactly once; they get the
     * order 1, 2, 3... in the order given.
     */
    public function __invoke(Request $request, string $resource)
    {
        abort_unless(isset(self::RESOURCES[$resource]), 404);
        [$model, $column, $cacheKeys] = self::RESOURCES[$resource];

        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|distinct',
        ]);

        $ids = array_map('intval', $request->input('ids'));
        $existing = $model::pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $given = $ids;
        sort($given);

        if ($given !== $existing) {
            throw ValidationException::withMessages([
                'ids' => ['تغيّرت القائمة منذ فتح الصفحة، حدّث الصفحة ثم حاول مرة أخرى.'],
            ]);
        }

        DB::transaction(function () use ($model, $column, $ids) {
            foreach ($ids as $position => $id) {
                $model::whereKey($id)->update([$column => $position + 1]);
            }
        });

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ الترتيب.',
        ]);
    }
}
