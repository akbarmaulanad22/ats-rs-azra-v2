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
        Schema::table('applications', function (Blueprint $table) {
            $table->text('alasan_melamar')->nullable()->after('cv_path');
            $table->string('gaji_diharapkan')->nullable()->after('alasan_melamar');
            $table->text('fasilitas_diharapkan')->nullable()->after('gaji_diharapkan');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['alasan_melamar', 'gaji_diharapkan', 'fasilitas_diharapkan']);
        });
    }
};
