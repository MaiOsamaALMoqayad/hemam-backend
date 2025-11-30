<?php

namespace Database\Seeders;

use App\Models\Camp;
use App\Models\CampLocation;
use App\Models\CampLearning;
use App\Models\CampActivity;
use App\Models\CampImage;
use Illuminate\Database\Seeder;

class CampSeeder extends Seeder
{
    public function run(): void
    {
        // Camp 1 - Open
        $camp1 = Camp::create([
            'title' => ['ar' => 'مخيم صيف 2025', 'en' => 'Summer Camp 2025'],
            'description' => ['ar' => 'مخيم تدريبي صيفي', 'en' => 'A summer training camp'],
            'about' => ['ar' => 'مخيم شامل لتطوير المهارات', 'en' => 'Comprehensive camp for skill development'],
            'main_image' => 'camps/main-camp3.jpeg',
            'age_range' => '14-17',
            'start_date' => '2025-07-15',
            'duration' => 7,
            'capacity' => 50,
            'is_open' => true,
            'status' => 'upcoming',
            'order' => 1,
        ]);

        CampLocation::create(['camp_id' => $camp1->id, 'name' => ['ar' => 'القدس', 'en' => 'Jerusalem'], 'order' => 1]);
        CampLocation::create(['camp_id' => $camp1->id, 'name' => ['ar' => 'رام الله', 'en' => 'Ramallah'], 'order' => 2]);

        CampLearning::create(['camp_id' => $camp1->id, 'title' => ['ar' => 'مهارات القيادة', 'en' => 'Leadership Skills'], 'order' => 1]);
        CampLearning::create(['camp_id' => $camp1->id, 'title' => ['ar' => 'التخطيط الاستراتيجي', 'en' => 'Strategic Planning'], 'order' => 2]);

        CampActivity::create([
            'camp_id' => $camp1->id,
            'title' => ['ar' => 'ورشة القيادة', 'en' => 'Leadership Workshop'],
            'description' => ['ar' => 'ورشة تفاعلية', 'en' => 'Interactive workshop'],
            'order' => 1,
        ]);

        CampImage::create(['camp_id' => $camp1->id, 'image' => 'camps/gallery/img1.jpeg', 'order' => 1]);
        CampImage::create(['camp_id' => $camp1->id, 'image' => 'camps/gallery/img2.jpeg', 'order' => 2]);

        // Camp 2 - Closed
        $camp2 = Camp::create([
            'title' => ['ar' => 'مخيم شتاء 2024', 'en' => 'Winter Camp 2024'],
            'description' => ['ar' => 'مخيم شتوي منتهي', 'en' => 'Completed winter camp'],
            'about' => ['ar' => 'مخيم ناجح', 'en' => 'Successful camp'],
            'main_image' => 'camps/winter.jpg',
            'age_range' => '13-16',
            'start_date' => '2024-12-20',
            'duration' => 10,
            'capacity' => 60,
            'is_open' => false,
            'status' => 'completed',
            'order' => 2,
        ]);

        CampLocation::create(['camp_id' => $camp2->id, 'name' => ['ar' => 'الخليل', 'en' => 'Hebron'], 'order' => 1]);
    }
}
