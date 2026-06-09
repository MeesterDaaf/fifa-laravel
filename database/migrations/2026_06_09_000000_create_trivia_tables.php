<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trivia_questions', function (Blueprint $table) {
            $table->id();
            $table->string('category', 32);
            $table->text('question');
            $table->json('options');
            $table->unsignedTinyInteger('correct_index');
            $table->text('explanation');
            // De dag waarop deze vraag de "vraag van de dag" was. Uniek → elke
            // vraag komt maar één keer voor.
            $table->date('used_on')->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('trivia_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trivia_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('choice');
            $table->timestamps();

            // Eén antwoord per deelnemer per vraag.
            $table->unique(['trivia_question_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trivia_votes');
        Schema::dropIfExists('trivia_questions');
    }
};
