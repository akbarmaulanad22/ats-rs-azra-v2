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
        Schema::create('candidate_work_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->string('nama_perusahaan');
            $table->string('jabatan');
            $table->text('alamat_perusahaan');
            $table->date('periode_mulai');
            $table->date('periode_selesai')->nullable();
            $table->text('rincian_tugas');
            $table->string('gaji_terakhir')->nullable();
            $table->text('alasan_meninggalkan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_work_experiences');
    }
};
