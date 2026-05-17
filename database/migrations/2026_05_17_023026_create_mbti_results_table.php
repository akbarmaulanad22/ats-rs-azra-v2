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
        Schema::create('mbti_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mbti_submission_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('skor_e')->default(0);
            $table->unsignedSmallInteger('skor_i')->default(0);
            $table->unsignedSmallInteger('skor_s')->default(0);
            $table->unsignedSmallInteger('skor_n')->default(0);
            $table->unsignedSmallInteger('skor_t')->default(0);
            $table->unsignedSmallInteger('skor_f')->default(0);
            $table->unsignedSmallInteger('skor_j')->default(0);
            $table->unsignedSmallInteger('skor_p')->default(0);
            $table->string('tipe', 4); // e.g. ENTJ
            $table->unsignedSmallInteger('kekuatan_ei')->default(0); // 0-100
            $table->unsignedSmallInteger('kekuatan_sn')->default(0);
            $table->unsignedSmallInteger('kekuatan_tf')->default(0);
            $table->unsignedSmallInteger('kekuatan_jp')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mbti_results');
    }
};
