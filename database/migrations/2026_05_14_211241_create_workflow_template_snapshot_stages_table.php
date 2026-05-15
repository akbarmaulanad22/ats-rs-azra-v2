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
        Schema::create('workflow_template_snapshot_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_template_snapshot_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->string('key');
            $table->string('nama');
            $table->boolean('is_locked_first')->default(false);
            $table->boolean('is_locked_last')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_template_snapshot_stages');
    }
};
