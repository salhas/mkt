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
        Schema::create('sar_participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sar_operation_id')->constrained('sar_operations')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('organization_name'); // Nama Potensi SAR: PMI, BPBD, Basarnas, Tagana, RS, dll
            $table->string('commander_name'); // Nama Danru / Koordinator Lapangan
            $table->string('contact_number'); // No Kontak / HT Repiter
            $table->integer('personnel_count')->default(1); // Jumlah Personel Diterjunkan
            $table->enum('status', ['Persiapan Mobilisasi', 'Dalam Perjalanan', 'Tiba di Posko Utama', 'Aktif Operasi Evakuasi', 'Selesai / Demobilisasi'])->default('Persiapan Mobilisasi');
            $table->string('departure_location')->nullable(); // Posko Asal / Keberangkatan
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('resources_deployed')->nullable(); // Perahu, Ambulance, Tenda, Genset, Logistik, Obat
            $table->text('preparation_notes')->nullable(); // Catatan Kesiapsiagaan / Rencana Kerja
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sar_participations');
    }
};
