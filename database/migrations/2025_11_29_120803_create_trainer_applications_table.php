<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainer_applications', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 100);
            $table->unsignedTinyInteger('age');
            $table->string('phone', 15);
            $table->string('country_code', 5);
            $table->string('email');
            $table->string('residence', 100);
            $table->enum('gender', ['male', 'female']);
            $table->enum('qualification', ['high_school', 'bachelor', 'master', 'other']);
            $table->string('qualification_other', 100)->nullable();
            $table->string('specialization', 100);
            $table->unsignedTinyInteger('experience_years');
            $table->string('program_name', 100);
            $table->json('social_links')->nullable();
            $table->boolean('has_previous_courses');
            $table->text('courses_description')->nullable();
            $table->text('course_outcomes');
            $table->text('about_me');
            $table->json('training_fields');
            $table->string('training_field_other', 100)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('created_at');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainer_applications');
    }
};
