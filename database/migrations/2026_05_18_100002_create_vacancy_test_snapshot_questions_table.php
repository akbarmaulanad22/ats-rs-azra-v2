<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacancy_test_snapshot_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_test_snapshot_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('urutan');
            $table->string('tipe');
            $table->text('pertanyaan');
            $table->unsignedInteger('nilai_poin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacancy_test_snapshot_questions');
    }
};
