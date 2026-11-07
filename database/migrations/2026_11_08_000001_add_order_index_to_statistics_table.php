<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Statistics can be reordered from the public site's in-place editor.
     * Existing rows keep their current (id) order.
     */
    public function up(): void
    {
        Schema::table('statistics', function (Blueprint $table) {
            $table->integer('order_index')->default(0)->after('icon_name');
        });

        DB::table('statistics')->update(['order_index' => DB::raw('id')]);
    }

    public function down(): void
    {
        Schema::table('statistics', function (Blueprint $table) {
            $table->dropColumn('order_index');
        });
    }
};
