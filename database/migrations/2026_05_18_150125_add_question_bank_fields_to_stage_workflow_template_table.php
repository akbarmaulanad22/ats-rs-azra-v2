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
        Schema::table('stage_workflow_template', function (Blueprint $table) {
            $table->foreignId('question_bank_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('batas_waktu_menit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stage_workflow_template', function (Blueprint $table) {
            $table->dropConstrainedForeignId('question_bank_id');
            $table->dropColumn('batas_waktu_menit');
        });
    }
};
