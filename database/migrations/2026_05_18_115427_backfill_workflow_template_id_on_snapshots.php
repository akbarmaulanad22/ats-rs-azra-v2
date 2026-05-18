<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('workflow_template_snapshots')
            ->whereNull('workflow_template_id')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('workflow_templates')
                    ->whereColumn('workflow_templates.nama', 'workflow_template_snapshots.nama');
            })
            ->update([
                'workflow_template_id' => DB::raw(
                    '(SELECT id FROM workflow_templates WHERE workflow_templates.nama = workflow_template_snapshots.nama LIMIT 1)'
                ),
            ]);
    }

    public function down(): void {}
};
