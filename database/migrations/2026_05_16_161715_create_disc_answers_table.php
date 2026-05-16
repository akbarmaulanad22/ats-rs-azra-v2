<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('disc_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disc_submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('disc_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('most_disc_word_id')->constrained('disc_question_words')->cascadeOnDelete();
            $table->foreignId('least_disc_word_id')->constrained('disc_question_words')->cascadeOnDelete();
            $table->unique(['disc_submission_id', 'disc_question_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disc_answers');
    }
};
