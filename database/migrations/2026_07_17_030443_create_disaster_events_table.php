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
        Schema::create('disaster_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category'); // Banjir, Longsor, Gempa, Kebakaran, Gunung Meletus, dll.
            $table->string('location');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('severity')->default('Sedang'); // Rendah, Sedang, Tinggi, Darurat
            $table->string('status')->default('Siaga'); // Siaga, Evakuasi, Pemulihan, Selesai
            $table->text('description')->nullable();
            $table->string('rescue_team_leader')->nullable(); // Pimpinan relawan rescue di lokasi
            $table->integer('victim_count')->default(0); // Jumlah pengungsi/korban
            $table->date('date_occurred');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disaster_events');
    }
};
