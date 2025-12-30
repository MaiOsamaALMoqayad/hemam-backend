<?php
namespace App\Observers;

use App\Models\Camp;
use Illuminate\Support\Facades\Cache;

class CampObserver
{
    // يعمل عند الإضافة والتعديل
    public function saved(Camp $camp)
    {
        $this->clearCampsCache($camp);
    }

    // يعمل عند الحذف
    public function deleted(Camp $camp)
    {
        $this->clearCampsCache($camp);
    }

    private function clearCampsCache(Camp $camp)
    {
        // مسح القوائم الرئيسية
        Cache::forget('camps:open');
        Cache::forget('camps:closed');
        Cache::forget('camps:all_separated');

        // مسح تفاصيل هذا المخيم تحديداً
        Cache::forget("camp:details:{$camp->id}");
    }
}
