<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('previous_rank')->nullable()->after('is_bot');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->timestamp('ranking_captured_at')->nullable()->after('whatsapp_group_url');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('previous_rank');
        });
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('ranking_captured_at');
        });
    }
};
