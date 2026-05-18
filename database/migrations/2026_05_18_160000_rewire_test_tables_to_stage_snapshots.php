<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_answers', function (Blueprint $table) {
            $table->dropForeign(['vacancy_test_snapshot_question_id']);
            $table->dropForeign(['vacancy_test_snapshot_option_id']);
            $table->dropColumn(['vacancy_test_snapshot_question_id', 'vacancy_test_snapshot_option_id']);

            $table->foreignId('stage_snapshot_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stage_snapshot_option_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('test_submissions', function (Blueprint $table) {
            $table->dropForeign(['vacancy_test_snapshot_id']);
            $table->dropColumn('vacancy_test_snapshot_id');

            $table->foreignId('workflow_template_snapshot_stage_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('test_submissions', function (Blueprint $table) {
            $table->dropForeign(['workflow_template_snapshot_stage_id']);
            $table->dropColumn('workflow_template_snapshot_stage_id');

            $table->foreignId('vacancy_test_snapshot_id')->constrained()->cascadeOnDelete();
        });

        Schema::table('test_answers', function (Blueprint $table) {
            $table->dropForeign(['stage_snapshot_question_id']);
            $table->dropForeign(['stage_snapshot_option_id']);
            $table->dropColumn(['stage_snapshot_question_id', 'stage_snapshot_option_id']);

            $table->foreignId('vacancy_test_snapshot_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vacancy_test_snapshot_option_id')->nullable()->constrained()->nullOnDelete();
        });
    }
};
