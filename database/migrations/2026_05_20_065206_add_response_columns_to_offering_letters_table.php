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
        Schema::table('offering_letters', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('sent_at');
            $table->timestamp('responded_at')->nullable()->after('status');
            $table->text('rejection_reason')->nullable()->after('responded_at');
        });
    }

    public function down(): void
    {
        Schema::table('offering_letters', function (Blueprint $table) {
            $table->dropColumn(['status', 'responded_at', 'rejection_reason']);
        });
    }
};
