<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::drop('interview_result_ratings');

        Schema::create('interview_result_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_result_id')->constrained()->cascadeOnDelete();
            $table->string('nama_kriteria');
            $table->tinyInteger('nilai');
            $table->unsignedBigInteger('interview_template_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::drop('interview_result_ratings');

        Schema::create('interview_result_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_result_id')->constrained()->cascadeOnDelete();
            $table->string('nama_kriteria');
            $table->tinyInteger('nilai');
            $table->timestamps();
        });
    }
};
