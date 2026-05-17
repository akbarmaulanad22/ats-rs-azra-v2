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
        Schema::create('mbti_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mbti_submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mbti_question_id')->constrained()->cascadeOnDelete();
            $table->string('pilihan', 1); // A or B
            $table->unique(['mbti_submission_id', 'mbti_question_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mbti_answers');
    }
};
