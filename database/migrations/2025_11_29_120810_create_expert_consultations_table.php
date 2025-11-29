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
        Schema::create('expert_consultations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('whatsapp', 20);
            $table->string('country_code', 5);
            $table->enum('consultation_type', ['educational', 'administrative', 'leadership', 'personal']);
            $table->text('consultation_details');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            // Indexes للأداء
            $table->index(['status', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expert_consultations');
    }
};
