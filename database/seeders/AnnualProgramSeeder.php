<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AnnualProgram;
use App\Models\AnnualProgramHistory;

class AnnualProgramSeeder extends Seeder
{
    public function run(): void
    {
        $program = AnnualProgram::create([
            'title' => [
                'ar' => 'برنامج القيادة الصيفي',
                'en' => 'Summer Leadership Program',
            ],
            'description' => [
                'ar' => 'برنامج قيادي مكثّف لمدة 8 أسابيع يستهدف تطوير مهارات الشباب من خلال أنشطة عملية ومشاريع جماعية.',
                'en' => 'An intensive 8-week program designed to develop the next generation of leaders through hands-on projects.',
            ],
            'image' => 'annual_programs/Almagd.jpg',
            'order' => 1,
            'is_open' => true,
            'application_deadline' => '15 مارس 2025',
            'duration' => '8 أسابيع',
            'capacity' => '50 مشارك',
        ]);

        // إضافة الـ history
        $program->histories()->createMany([
            [
                'year' => 2024,
                'image' => 'annual_programs/annual11.jpg',
                'achievements' => [
                    'تنفيذ 12 مشروعًا مجتمعيًا مؤثرًا',
                    'حصول 85% من المشاركين على شهادات قيادة',
                    'الشراكة مع 8 شركات كبرى',
                    'جمع تبرعات بقيمة 250,000 دولار',
                ],
            ],
            [
                'year' => 2023,
                'image' => 'annual_programs/history_2023.jpg',
                'achievements' => [
                    'تنفيذ 10 مشاريع مجتمعية ناجحة',
                    'معدل رضا 92% من المشاركين',
                    'شراكات مع 6 مؤسسات دولية',
                    'شبكة الخريجين تجاوزت 500 عضو',
                ],
            ],
            [
                'year' => 2022,
                'image' => 'annual_programs/history_2022.jpg',
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
