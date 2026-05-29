<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->dropColumn(['first_yellow_card_minute', 'points_yellow']);
        });

        Schema::table('fixtures', function (Blueprint $table) {
            $table->dropColumn('first_yellow_card_minute');
        });
    }

    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->integer('first_yellow_card_minute')->nullable();
            $table->integer('points_yellow')->default(0);
        });

        Schema::table('fixtures', function (Blueprint $table) {
            $table->integer('first_yellow_card_minute')->nullable();
        });
    }
};
