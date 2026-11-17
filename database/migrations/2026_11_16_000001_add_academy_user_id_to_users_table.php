<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cross-site login (SSO) with the academy (hemam-academy-api).
 * `academy_user_id` is the id of the matching account in the academy's
 * `users` table. The academy stores the reverse link (`site_user_id`); SSO
 * works only when both sides point at each other, so neither database alone
 * can grant access to the other system. Set with `php artisan sso:link`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('academy_user_id')->nullable()->unique()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['academy_user_id']);
            $table->dropColumn('academy_user_id');
        });
    }
};
