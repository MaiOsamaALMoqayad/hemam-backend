<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\ActivityHistory;

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $activity = Activity::create([
            'title' => [
                'ar' => 'نشاط القيادة الصيفي',
                'en' => 'Summer Leadership Activity',
            ],
            'description' => [
                'ar' => 'نشاط قيادي مكثّف لمدة 8 أسابيع لتطوير مهارات الشباب من خلال مشاريع عملية.',
                'en' => 'An intensive 8-week activity designed to develop the next generation of leaders through hands-on projects.',
            ],
            'image' => 'activities/Almagd.jpg',
            'season' => 'summer',
            'order' => 1,
            'is_open' => true,
            'application_deadline' => '15 مارس 2025',
            'duration' => '8 أسابيع',
            'capacity' => '50 مشارك',
        ]);

        // إضافة الـ history
        $activity->activity_histories()->createMany([
            [
                'year' => 2024,
                'achievements' => [
                    'تنفيذ 12 مشروعًا مجتمعيًا مؤثرًا',
                    'حصول 85% من المشاركين على شهادات قيادة',
                    'الشراكة مع 8 شركات كبرى',
                    'جمع تبرعات بقيمة 250,000 دولار',
                ],
            ],
            [
                'year' => 2023,
                'achievements' => [
                    'تنفيذ 10 مشاريع مجتمعية ناجحة',
                    'معدل رضا 92% من المشاركين',
                    'شراكات مع 6 مؤسسات دولية',
                    'شبكة الخريجين تجاوزت 500 عضو',
                ],
            ],
            [
                'year' => 2022,
                'achievements' => [
                    'تخرج أول دفعة تضم 40 مشاركًا',
                    'تنفيذ 8 مبادرات مجتمعية مستدامة',
                    'الحصول على جائزة "القيادة الوطنية للشباب"',
                    'تحسن مهارات 100% من المشاركين',
                ],
            ],
        ]);
    }
}
