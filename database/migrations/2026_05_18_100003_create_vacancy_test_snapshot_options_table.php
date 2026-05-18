<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacancy_test_snapshot_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_test_snapshot_question_id')->constrained()->cascadeOnDelete();
            $table->text('teks_opsi');
            $table->boolean('is_correct')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacancy_test_snapshot_options');
    }
};
