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
        if (! Schema::hasColumn('candidates', 'talent_pool_flagged_at')) {
            return;
        }

        Schema::table('candidates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('talent_pool_flagged_by');
            $table->dropColumn(['talent_pool_flagged_at', 'talent_pool_reason']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->timestamp('talent_pool_flagged_at')->nullable()->after('vaksinasi_covid');
            $table->foreignId('talent_pool_flagged_by')->nullable()->after('talent_pool_flagged_at')->constrained('users')->nullOnDelete();
            $table->text('talent_pool_reason')->nullable()->after('talent_pool_flagged_by');
        });
    }
};
