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
        Schema::table('vacancies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('workflow_template_id');
            $table->foreignId('workflow_template_snapshot_id')
                ->after('unit_id')
                ->constrained('workflow_template_snapshots')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vacancies', function (Blueprint $table) {
            $table->dropColumn('workflow_template_snapshot_id');
            $table->foreignId('workflow_template_id')
                ->after('unit_id')
                ->constrained()
                ->restrictOnDelete();
        });
    }
};
