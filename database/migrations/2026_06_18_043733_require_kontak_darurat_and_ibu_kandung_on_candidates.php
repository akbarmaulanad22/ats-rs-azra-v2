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
        Schema::table('candidates', function (Blueprint $table) {
            $table->string('nama_ibu_kandung')->nullable(false)->change();
            $table->string('kontak_darurat_nama')->nullable(false)->change();
            $table->string('kontak_darurat_no_telp', 20)->nullable(false)->change();
            $table->string('kontak_darurat_hubungan', 100)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->string('nama_ibu_kandung')->nullable()->change();
            $table->string('kontak_darurat_nama')->nullable()->change();
            $table->string('kontak_darurat_no_telp', 20)->nullable()->change();
            $table->string('kontak_darurat_hubungan')->nullable()->change();
        });
    }
};
