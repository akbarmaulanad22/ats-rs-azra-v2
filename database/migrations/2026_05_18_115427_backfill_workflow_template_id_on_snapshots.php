<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            UPDATE workflow_template_snapshots s
            SET workflow_template_id = t.id
            FROM workflow_templates t
            WHERE s.nama = t.nama
              AND s.workflow_template_id IS NULL
        ');
    }

    public function down(): void {}
};
