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
        Schema::table('partners', function (Blueprint $table) {
            if (!Schema::hasColumn('partners', 'tagline')) {
                $table->string('tagline')->nullable()->after('category');
            }
            if (!Schema::hasColumn('partners', 'readiness_status')) {
                $table->string('readiness_status', 100)->default('Siaga Operasi 24/7')->nullable()->after('mou_number');
            }
            if (!Schema::hasColumn('partners', 'recruitment_status')) {
                $table->string('recruitment_status', 50)->default('Buka')->nullable()->after('readiness_status');
            }
            if (!Schema::hasColumn('partners', 'membership_terms')) {
                $table->text('membership_terms')->nullable()->after('mission');
            }
            if (!Schema::hasColumn('partners', 'pillar_pre')) {
                $table->text('pillar_pre')->nullable()->after('membership_terms');
            }
            if (!Schema::hasColumn('partners', 'pillar_during')) {
                $table->text('pillar_during')->nullable()->after('pillar_pre');
            }
            if (!Schema::hasColumn('partners', 'pillar_post')) {
                $table->text('pillar_post')->nullable()->after('pillar_during');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $columns = [
                'tagline',
                'readiness_status',
                'recruitment_status',
                'membership_terms',
                'pillar_pre',
                'pillar_during',
                'pillar_post'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('partners', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
