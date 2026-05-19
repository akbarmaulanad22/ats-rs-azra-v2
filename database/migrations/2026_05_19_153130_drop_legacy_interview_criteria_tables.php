<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('vacancy_interview_criteria');
        Schema::dropIfExists('interview_criteria');
    }

    public function down(): void
    {
        Schema::create('interview_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('stage_key');
            $table->string('nama');
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('vacancy_interview_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->constrained()->cascadeOnDelete();
            $table->string('stage_key');
            $table->string('nama');
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });
    }
};
