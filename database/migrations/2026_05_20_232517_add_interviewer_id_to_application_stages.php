<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_stages', function (Blueprint $table) {
            $table->foreignId('interviewer_id')->nullable()->constrained('users')->nullOnDelete()->after('reviewed_by');
        });

        DB::table('stages')->where('key', 'wawancara_kepala_unit')->update([
            'key' => 'wawancara_user',
            'nama' => 'Wawancara User',
        ]);

        DB::table('application_stages')->where('key', 'wawancara_kepala_unit')->update([
            'key' => 'wawancara_user',
            'nama' => 'Wawancara User',
        ]);
    }

    public function down(): void
    {
        DB::table('stages')->where('key', 'wawancara_user')->update([
            'key' => 'wawancara_kepala_unit',
            'nama' => 'Wawancara Kepala Unit',
        ]);

        DB::table('application_stages')->where('key', 'wawancara_user')->update([
            'key' => 'wawancara_kepala_unit',
            'nama' => 'Wawancara Kepala Unit',
        ]);

        Schema::table('application_stages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('interviewer_id');
        });
    }
};
