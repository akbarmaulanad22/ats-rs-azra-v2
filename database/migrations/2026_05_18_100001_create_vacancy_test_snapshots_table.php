<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacancy_test_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_test_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('batas_waktu_menit');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacancy_test_snapshots');
    }
};
