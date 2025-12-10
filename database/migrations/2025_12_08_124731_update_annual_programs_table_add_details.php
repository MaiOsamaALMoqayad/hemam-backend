<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::table('annual_programs', function (Blueprint $table) {
        // حذف is_active
        $table->dropColumn('is_active');

        // إضافة الحقول الجديدة
        $table->boolean('is_open')->default(true);
        $table->string('application_deadline')->nullable();
        $table->string('duration')->nullable();
        $table->string('capacity')->nullable();

        // history كـ JSON
        $table->json('history')->nullable();
    });
}

public function down(): void
{
    Schema::table('annual_programs', function (Blueprint $table) {

        $table->boolean('is_active')->default(true);

        $table->dropColumn([
            'is_open',
            'application_deadline',
            'duration',
            'capacity',
            'history',
        ]);
    });
}
};
