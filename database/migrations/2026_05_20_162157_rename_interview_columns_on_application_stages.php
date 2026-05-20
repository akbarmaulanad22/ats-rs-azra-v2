<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_stages', function (Blueprint $table) {
            $table->renameColumn('jadwal_interview', 'jadwal');
            $table->renameColumn('lokasi_interview', 'lokasi');
        });
    }

    public function down(): void
    {
        Schema::table('application_stages', function (Blueprint $table) {
            $table->renameColumn('jadwal', 'jadwal_interview');
            $table->renameColumn('lokasi', 'lokasi_interview');
        });
    }
};
