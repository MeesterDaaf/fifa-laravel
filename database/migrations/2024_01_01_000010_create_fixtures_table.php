<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();
            $table->integer('external_id')->unique()->nullable();
            $table->string('home_team');
            $table->string('away_team');
            $table->string('home_team_code', 10)->nullable();
            $table->string('away_team_code', 10)->nullable();
            $table->datetime('scheduled_at');
            $table->string('stage')->default('GROUP_STAGE');
            $table->string('match_group')->nullable();
            $table->string('status')->default('SCHEDULED'); // SCHEDULED, IN_PLAY, FINISHED
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();
            $table->integer('first_goal_minute')->nullable();
            $table->integer('first_yellow_card_minute')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixtures');
    }
};
