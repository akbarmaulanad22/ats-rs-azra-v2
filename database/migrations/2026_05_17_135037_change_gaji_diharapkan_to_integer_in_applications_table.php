<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("UPDATE applications SET gaji_diharapkan = regexp_replace(gaji_diharapkan, '[^0-9]', '', 'g')");
            DB::statement("UPDATE applications SET gaji_diharapkan = NULL WHERE gaji_diharapkan = '' OR gaji_diharapkan IS NULL");
            DB::statement('ALTER TABLE applications ALTER COLUMN gaji_diharapkan TYPE INTEGER USING gaji_diharapkan::integer');
        } else {
            Schema::table('applications', function (Blueprint $table) {
                $table->integer('gaji_diharapkan')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('gaji_diharapkan')->nullable()->change();
        });
    }
};
