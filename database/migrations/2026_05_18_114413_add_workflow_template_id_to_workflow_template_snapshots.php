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
        Schema::table('workflow_template_snapshots', function (Blueprint $table) {
            $table->foreignId('workflow_template_id')->nullable()->after('nama')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflow_template_snapshots', function (Blueprint $table) {
            $table->dropForeign(['workflow_template_id']);
            $table->dropColumn('workflow_template_id');
        });
    }
};
