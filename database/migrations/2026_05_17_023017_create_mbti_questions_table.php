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
        Schema::create('mbti_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('urutan')->unique();
            $table->string('dikotomi', 2); // EI, SN, TF, JP
            $table->text('pernyataan_a');
            $table->string('kutub_a', 1); // E, I, S, N, T, F, J, P
            $table->text('pernyataan_b');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mbti_questions');
    }
};
