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
        Schema::table('workflow_template_snapshot_stages', function (Blueprint $table) {
            $table->unsignedBigInteger('question_bank_id')->nullable();
            $table->unsignedInteger('batas_waktu_menit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflow_template_snapshot_stages', function (Blueprint $table) {
            $table->dropColumn(['question_bank_id', 'batas_waktu_menit']);
        });
    }
};
