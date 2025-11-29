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
            AnnualProgramSeeder::class,
            ProjectSeeder::class,
            CampSeeder::class,
            TrainerSeeder::class,
        ]);

        $this->command->info('✅ All seeders completed!');
    }
}
