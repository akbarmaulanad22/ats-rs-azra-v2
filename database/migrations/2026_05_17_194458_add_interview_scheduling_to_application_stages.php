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
        Schema::table('application_stages', function (Blueprint $table) {
            $table->dateTime('jadwal_interview')->nullable()->after('catatan');
            $table->string('lokasi_interview')->nullable()->after('jadwal_interview');
        });
    }

    public function down(): void
    {
        Schema::table('application_stages', function (Blueprint $table) {
            $table->dropColumn(['jadwal_interview', 'lokasi_interview']);
        });
    }
};
