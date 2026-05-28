<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fixture_id')->constrained()->cascadeOnDelete();
            $table->integer('home_score')->default(0);
            $table->integer('away_score')->default(0);
            $table->integer('first_goal_minute')->nullable();
            $table->integer('first_yellow_card_minute')->nullable();
            $table->integer('points_score')->default(0);
            $table->integer('points_yellow')->default(0);
            $table->integer('points_goal_minute')->default(0);
            $table->integer('total_points')->default(0);
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'fixture_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};
