<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema, DB};

return new class extends Migration
{
    public function up(): void
    {
        // تحديث القيم الموجودة
        DB::table('expert_consultations')
            ->where('consultation_type', 'administrative')
            ->update(['consultation_type' => 'management']);

        // تعديل الـ enum
        DB::statement("ALTER TABLE expert_consultations MODIFY COLUMN consultation_type ENUM('educational', 'management', 'leadership', 'personal') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE expert_consultations MODIFY COLUMN consultation_type ENUM('educational', 'administrative', 'leadership', 'personal') NOT NULL");

        DB::table('expert_consultations')
            ->where('consultation_type', 'management')
            ->update(['consultation_type' => 'administrative']);
    }
};
