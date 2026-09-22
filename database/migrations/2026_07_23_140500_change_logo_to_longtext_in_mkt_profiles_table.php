<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE mkt_profiles MODIFY logo LONGTEXT NULL');
        } else {
            Schema::table('mkt_profiles', function (Blueprint $table) {
                $table->longText('logo')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE mkt_profiles MODIFY logo VARCHAR(255) NULL');
        } else {
            Schema::table('mkt_profiles', function (Blueprint $table) {
                $table->string('logo', 255)->nullable()->change();
            });
        }
    }
};
