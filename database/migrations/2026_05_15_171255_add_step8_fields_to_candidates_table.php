<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->boolean('pernah_sakit_serius')->nullable()->after('is_fresh_graduate');
            $table->text('diagnosis_sakit')->nullable()->after('pernah_sakit_serius');
            $table->string('vaksinasi_covid')->nullable()->after('diagnosis_sakit');
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn(['pernah_sakit_serius', 'diagnosis_sakit', 'vaksinasi_covid']);
        });
    }
};
