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
        Schema::create('interview_readiness_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_result_id')->constrained()->cascadeOnDelete();
            $table->string('pertanyaan');
            $table->boolean('jawaban');
            $table->foreignId('interview_template_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_readiness_answers');
    }
};
