<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('camps', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('description');
            $table->json('about')->nullable();
            $table->string('main_image');
            $table->string('age_range', 20);
            $table->date('start_date');
            $table->unsignedTinyInteger('duration');
            $table->unsignedSmallInteger('capacity');
            $table->boolean('is_open')->default(true);
            $table->enum('status', ['upcoming', 'ongoing', 'completed'])->default('upcoming');
            $table->unsignedTinyInteger('order')->default(0);
            $table->timestamps();

            $table->index(['is_open', 'status', 'start_date']);
            $table->index(['status', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('camps');
    }
};
