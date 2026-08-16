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
        Schema::table('sar_operations', function (Blueprint $table) {
            if (!Schema::hasColumn('sar_operations', 'deployed_teams')) {
                $table->text('deployed_teams')->nullable()->after('potensi_sar');
            }
            if (!Schema::hasColumn('sar_operations', 'standby_teams')) {
                $table->text('standby_teams')->nullable()->after('deployed_teams');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sar_operations', function (Blueprint $table) {
            $table->dropColumn(['deployed_teams', 'standby_teams']);
        });
    }
};
