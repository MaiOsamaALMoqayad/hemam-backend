<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            StatisticsSeeder::class,
            SettingsSeeder::class,
            ActivitySeeder::class,
            ProjectSeeder::class,
            TrainerSeeder::class,
            KhaterCategorySeeder::class,
        ]);

        $this->command->info('✅ All seeders completed!');
    }
}
