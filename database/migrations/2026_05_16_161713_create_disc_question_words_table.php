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
        Schema::create('disc_question_words', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disc_question_id')->constrained()->cascadeOnDelete();
            $table->string('teks');
            $table->string('dimensi'); // D, I, S, C
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disc_question_words');
    }
};
