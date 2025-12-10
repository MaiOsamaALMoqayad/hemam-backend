<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annual_program_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('annual_program_id')->constrained()->onDelete('cascade');
            $table->integer('year');
            $table->string('image')->nullable();
            $table->json('achievements')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annual_program_histories');
    }
};
