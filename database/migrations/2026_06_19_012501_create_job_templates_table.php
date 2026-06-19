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
        Schema::create('job_templates', function (Blueprint $table) {
            $table->id();
            $table->string('judul_posisi');
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();
            $table->foreignId('workflow_template_id')->constrained()->restrictOnDelete();
            $table->string('jenis_pekerjaan');
            $table->text('deskripsi_pekerjaan');
            $table->text('kualifikasi');
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_templates');
    }
};
