<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('test_answers');
        Schema::dropIfExists('test_submissions');

        Schema::create('test_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vacancy_test_snapshot_id')->constrained()->cascadeOnDelete();
            $table->string('token')->unique();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedInteger('total_skor')->nullable();
            $table->timestamps();
        });

        Schema::create('test_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vacancy_test_snapshot_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vacancy_test_snapshot_option_id')->nullable()->constrained()->nullOnDelete();
            $table->text('jawaban_teks')->nullable();
            $table->unsignedInteger('skor')->nullable();
            $table->boolean('is_reviewed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_answers');
        Schema::dropIfExists('test_submissions');

        Schema::create('test_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vacancy_test_id')->constrained()->cascadeOnDelete();
            $table->string('token')->unique();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedInteger('total_skor')->nullable();
            $table->timestamps();
        });

        Schema::create('test_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_option_id')->nullable()->constrained()->nullOnDelete();
            $table->text('jawaban_teks')->nullable();
            $table->unsignedInteger('skor')->nullable();
            $table->boolean('is_reviewed')->default(false);
            $table->timestamps();
        });
    }
};
