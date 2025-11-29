<?php

namespace Database\Seeders;

use App\Models\Statistics;
use Illuminate\Database\Seeder;

class StatisticsSeeder extends Seeder
{
    public function run(): void
    {
        Statistics::create([
            'beneficiaries_count' => 1500,
            'institutions_count' => 25,
            'trainings_count' => 150,
            'consultations_count' => 300,
        ]);
    }
}
