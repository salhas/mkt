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
        Schema::create('sar_operations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->enum('type', ['Operasi SAR', 'Siaga SAR'])->default('Operasi SAR');
            $table->string('location');
            $table->decimal('latitude', 10, 7)->default(-5.147665);
            $table->decimal('longitude', 10, 7)->default(119.432731);
            $table->string('status')->default('Operasi Aktif'); // Direncanakan, Siaga SAR, Operasi Aktif, Selesai, Ditutup
            $table->string('severity_level')->default('Tinggi'); // Rendah, Sedang, Tinggi, Siaga 1
            $table->string('commander_name')->nullable(); // SMC (SAR Mission Commander) / Danru
            $table->integer('personnel_count')->default(1);
            $table->text('potensi_sar')->nullable(); // Instansi/Potensi SAR: Basarnas, BPBD, PMI, MKT Rescue
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->text('equipment_used')->nullable(); // Perahu Karet, Drone, Alkon, P3K, dll
            $table->integer('victims_saved')->default(0);
            $table->integer('victims_injured')->default(0);
            $table->integer('victims_deceased')->default(0);
            $table->integer('victims_missing')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sar_operations');
    }
};
