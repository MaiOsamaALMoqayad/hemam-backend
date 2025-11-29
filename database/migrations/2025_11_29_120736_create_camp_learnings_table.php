<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('camp_learnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camp_id')->constrained()->onDelete('cascade');
            $table->json('title');
            $table->unsignedTinyInteger('order')->default(0);

            $table->index(['camp_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('camp_learnings');
    }
};
