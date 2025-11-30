<?php

namespace Database\Seeders;

use App\Models\Trainer;
use Illuminate\Database\Seeder;

class TrainerSeeder extends Seeder
{
    public function run(): void
    {
        $trainers = [
            [
                'name' => ['ar' => 'أحمد محمد', 'en' => 'Ahmad Mohammad'],
                'image' => 'trainers/T2.jpg',
                'bio' => ['ar' => 'مدرب معتمد في القيادة', 'en' => 'Certified leadership trainer'],
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'فاطمة أحمد', 'en' => 'Fatima Ahmad'],
                'image' => 'trainers/T1.jpg',
                'bio' => ['ar' => 'مدربة في التربية', 'en' => 'Education trainer'],
                'order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($trainers as $trainer) {
            Trainer::create($trainer);
        }
    }
}
