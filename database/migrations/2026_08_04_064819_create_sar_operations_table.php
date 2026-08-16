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
        if (!Schema::hasTable('sar_operations')) {
            Schema::create('sar_operations', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('location');
                $table->string('team_name')->default('Tim Rescue Gabungan MKT & BASARNAS');
                $table->string('team_leader')->default('Ahmad Roni (Danpos SAR)');
                $table->string('status')->default('AKTIF'); // AKTIF, SIAGA, EVAKUASI, PEMULIHAN, SELESAI
                $table->string('severity')->default('Darurat'); // Darurat, High, Medium, Low
                $table->integer('victim_count')->default(0);
                $table->text('equipment')->nullable();
                $table->text('description')->nullable();
                $table->text('image_url')->nullable();
                $table->string('start_date')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sar_operations');
    }
};
