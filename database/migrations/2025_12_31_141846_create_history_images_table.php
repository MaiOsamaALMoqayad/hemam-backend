<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_history_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_history_id')
                  ->constrained('activity_histories')
                  ->cascadeOnDelete();
            $table->string('image');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_history_images');
    }
};
