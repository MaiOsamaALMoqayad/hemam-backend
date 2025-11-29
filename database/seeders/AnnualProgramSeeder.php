<?php

namespace Database\Seeders;

use App\Models\AnnualProgram;
use Illuminate\Database\Seeder;

class AnnualProgramSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [
            [
                'title' => ['ar' => 'رواحل المجد', 'en' => 'Glory Caravans'],
                'description' => ['ar' => 'رحلة روحانية لأداء مناسك العمرة', 'en' => 'A spiritual journey to perform Umrah'],
                'image' => 'annual_programs/almagd.jpg',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => ['ar' => 'برنامج القيادة الشبابية', 'en' => 'Youth Leadership Program'],
                'description' => ['ar' => 'برنامج لتطوير المهارات القيادية', 'en' => 'A program to develop leadership skills'],
                'image' => 'annual_programs/leadership.jpg',
                'order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($programs as $program) {
            AnnualProgram::create($program);
        }
    }
}
