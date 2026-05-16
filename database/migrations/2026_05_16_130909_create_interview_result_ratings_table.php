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
        Schema::create('interview_result_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_result_id')->constrained()->cascadeOnDelete();
            $table->string('nama_kriteria');
            $table->tinyInteger('nilai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_result_ratings');
    }
};
