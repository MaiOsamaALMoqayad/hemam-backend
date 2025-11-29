<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectImage;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $project1 = Project::create([
            'title' => ['ar' => 'مخيم بَصمتي القيادي', 'en' => 'My Leadership Footprint Camp'],
            'description' => ['ar' => 'مشروع ريادي لتطوير مهارات الشباب', 'en' => 'A pioneering project to develop youth skills'],
            'order' => 1,
            'is_active' => true,
        ]);

        ProjectImage::create(['project_id' => $project1->id, 'image' => 'projects/proj21.jpeg', 'order' => 1]);
        ProjectImage::create(['project_id' => $project1->id, 'image' => 'projects/proj22.jpeg', 'order' => 2]);
        ProjectImage::create(['project_id' => $project1->id, 'image' => 'projects/proj23.jpeg', 'order' => 3]);

        $project2 = Project::create([
            'title' => ['ar' => 'مشروع تمكين المرأة', 'en' => 'Women Empowerment Project'],
            'description' => ['ar' => 'مشروع لتمكين المرأة وتطوير مهاراتها', 'en' => 'A project to empower women'],
            'order' => 2,
            'is_active' => true,
        ]);

        ProjectImage::create(['project_id' => $project2->id, 'image' => 'projects/women1.jpg', 'order' => 1]);
        ProjectImage::create(['project_id' => $project2->id, 'image' => 'projects/women2.jpg', 'order' => 2]);
    }
}
