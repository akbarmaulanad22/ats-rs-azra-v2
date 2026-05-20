<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('mcu_results');

        Schema::create('mcu_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('application_stage_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->restrictOnDelete();
            $table->string('keputusan');
            $table->string('dokumen_path')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mcu_results');

        Schema::create('mcu_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('dijadwalkan');
            $table->string('dokumen_path')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }
};
