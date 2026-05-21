<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nip')->unique();
            $table->string('nama_karyawan');
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();
            $table->string('posisi_pekerjaan');
            $table->string('profesi');
            $table->string('jabatan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
