<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('khater_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('khater_category_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('khater_posts');
    }
};
