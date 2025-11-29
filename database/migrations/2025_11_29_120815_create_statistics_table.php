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
        Schema::create('statistics', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('beneficiaries_count')->default(0);
            $table->unsignedInteger('institutions_count')->default(0);
            $table->unsignedInteger('trainings_count')->default(0);
            $table->unsignedInteger('consultations_count')->default(0);
            $table->timestamp('updated_at')->useCurrent();

            // Indexes للأداء
            $table->index('beneficiaries_count');
            $table->index('institutions_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statistics');
    }
};
