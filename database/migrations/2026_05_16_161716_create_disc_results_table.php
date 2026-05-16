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
        Schema::create('disc_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disc_submission_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('skor_d');
            $table->unsignedTinyInteger('skor_i');
            $table->unsignedTinyInteger('skor_s');
            $table->unsignedTinyInteger('skor_c');
            $table->string('tipe_primer'); // D, I, S, or C
            $table->string('tipe_sekunder'); // D, I, S, or C
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disc_results');
    }
};
