<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\KhaterCategory;

class KhaterCategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'تربويات',
                'description' => 'مقالات ومواضيع تربوية هادفة'
            ],
            [
                'name' => 'قائد النهضة',
                'description' => 'صفات القائد وصناعة التأثير'
            ],
            [
                'name' => 'على درب النجاح',
                'description' => 'خطوات عملية للنجاح والتميز'
            ],
        ];

        foreach ($categories as $category) {
            KhaterCategory::create($category);
        }
    }
}

