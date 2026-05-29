<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournament_predictions', function (Blueprint $table) {
            $table->integer('total_yellow_cards')->nullable()->after('top_scorer');
            $table->integer('total_red_cards')->nullable()->after('total_yellow_cards');
            $table->string('champion')->nullable()->after('total_red_cards');

            // Puntenuitsplitsing per onderdeel (totaal blijft in 'points').
            $table->integer('points_top_scorer')->default(0)->after('points');
            $table->integer('points_yellow')->default(0)->after('points_top_scorer');
            $table->integer('points_red')->default(0)->after('points_yellow');
            $table->integer('points_champion')->default(0)->after('points_red');
        });

        Schema::table('tournament_results', function (Blueprint $table) {
            $table->integer('total_yellow_cards')->nullable()->after('top_scorer');
            $table->integer('total_red_cards')->nullable()->after('total_yellow_cards');
            $table->string('champion')->nullable()->after('total_red_cards');
        });
    }

    public function down(): void
    {
        Schema::table('tournament_predictions', function (Blueprint $table) {
            $table->dropColumn([
                'total_yellow_cards', 'total_red_cards', 'champion',
                'points_top_scorer', 'points_yellow', 'points_red', 'points_champion',
            ]);
        });

        Schema::table('tournament_results', function (Blueprint $table) {
            $table->dropColumn(['total_yellow_cards', 'total_red_cards', 'champion']);
        });
    }
};
