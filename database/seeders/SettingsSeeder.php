<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'site_name',
                'value' => ['ar' => 'همم للتطوير والتدريب', 'en' => 'Hemam Development & Training'],
                'type' => 'text',
                'group' => 'general',
            ],
            [
                'key' => 'about_us',
                'value' => ['ar' => 'نحن مؤسسة تهدف إلى تطوير مهارات الشباب', 'en' => 'We are an organization dedicated to developing youth skills'],
                'type' => 'textarea',
                'group' => 'general',
            ],
            [
                'key' => 'contact_email',
                'value' => ['ar' => 'info@hemam.com', 'en' => 'info@hemam.com'],
                'type' => 'text',
                'group' => 'contact',
            ],
            [
                'key' => 'contact_phone',
                'value' => ['ar' => '+970599123456', 'en' => '+970599123456'],
                'type' => 'text',
                'group' => 'contact',
            ],
            [
                'key' => 'facebook_url',
                'value' => ['ar' => 'https://facebook.com/hemam', 'en' => 'https://facebook.com/hemam'],
                'type' => 'text',
                'group' => 'social',
            ],
            [
                'key' => 'instagram_url',
                'value' => ['ar' => 'https://instagram.com/hemam', 'en' => 'https://instagram.com/hemam'],
                'type' => 'text',
                'group' => 'social',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
