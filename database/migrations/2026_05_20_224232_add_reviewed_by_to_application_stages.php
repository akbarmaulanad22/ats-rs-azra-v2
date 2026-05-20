<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_stages', function (Blueprint $table) {
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete()->after('lokasi');
        });
    }

    public function down(): void
    {
        Schema::table('application_stages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
        });
    }
};
