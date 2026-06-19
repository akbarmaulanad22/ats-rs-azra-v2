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
        Schema::create('job_template_interview_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('interview_template_id')->constrained()->cascadeOnDelete();
            $table->string('stage_key');
            $table->timestamps();

            $table->unique(['job_template_id', 'interview_template_id', 'stage_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_template_interview_templates');
    }
};
