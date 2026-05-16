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
        Schema::create('vacancy_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('batas_waktu_menit')->default(60);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacancy_tests');
    }
};
