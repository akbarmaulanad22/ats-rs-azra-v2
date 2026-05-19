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
        Schema::table('questions', function (Blueprint $table) {
            if (Schema::hasColumn('questions', 'unit_id')) {
                $table->dropConstrainedForeignId('unit_id');
            }
            $table->foreignId('question_bank_template_id')->after('id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('urutan')->after('nilai_poin')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['question_bank_template_id']);
            $table->dropColumn(['question_bank_template_id', 'urutan']);
            $table->foreignId('unit_id')->after('id')->constrained()->cascadeOnDelete();
        });
    }
};
