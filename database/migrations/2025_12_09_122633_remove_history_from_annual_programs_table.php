<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('annual_programs', function (Blueprint $table) {
            $table->dropColumn('history');
        });
    }

    public function down(): void
    {
        Schema::table('annual_programs', function (Blueprint $table) {
            $table->json('history')->nullable();
        });
    }
};
