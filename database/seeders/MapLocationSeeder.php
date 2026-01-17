<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\MapLocation;

class MapLocationSeeder extends Seeder
{
    public function run(): void
    {
        MapLocation::insert([
            [
                'name' => 'عمان',
                'latitude' => 31.9454,
                'longitude' => 35.9284,
                'description' => 'قمنا بـ 1 2 3.',
            ],
            [
                'name' => 'إربيد',
                'latitude' => 32.5514,
                'longitude' => 35.8515,
                'description' => 'قمنا بـ 1 2 3.',
            ],
            [
                'name' => 'الزرقاء',
                'latitude' => 32.0608,
                'longitude' => 36.0942,
                'description' => 'قمنا بـ 1 2 3.',
            ],
            [
                'name' => 'أكابا',
                'latitude' => 29.5319,
                'longitude' => 35.0061,
                'description' => 'قمنا بـ 1 2 3.',
            ],
        ]);
    }
}
