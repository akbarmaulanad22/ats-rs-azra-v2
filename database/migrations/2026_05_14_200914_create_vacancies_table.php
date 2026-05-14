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
        Schema::create('vacancies', function (Blueprint $table) {
            $table->id();
            $table->string('judul_posisi');
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();
            $table->foreignId('workflow_template_id')->constrained()->restrictOnDelete();
            $table->string('jenis_pekerjaan');
            $table->text('deskripsi_pekerjaan');
            $table->text('kualifikasi');
            $table->unsignedInteger('jumlah_posisi');
            $table->date('tenggat_lamaran');
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacancies');
    }
};
