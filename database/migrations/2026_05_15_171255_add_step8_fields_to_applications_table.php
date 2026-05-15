<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->text('kesiapan_kerja')->nullable()->after('fasilitas_diharapkan');
            $table->string('str_sip_path')->nullable()->after('kesiapan_kerja');
            $table->string('sumber_informasi')->nullable()->after('str_sip_path');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['kesiapan_kerja', 'str_sip_path', 'sumber_informasi']);
        });
    }
};
