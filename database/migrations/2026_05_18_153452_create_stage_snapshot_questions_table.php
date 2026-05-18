<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stage_snapshot_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_template_snapshot_stage_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('urutan');
            $table->string('tipe');
            $table->text('pertanyaan');
            $table->unsignedInteger('nilai_poin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stage_snapshot_questions');
    }
};
